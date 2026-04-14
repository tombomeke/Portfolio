<?php
// app/Services/ProjectRoadmapService.php — sync roadmap TODOs from ReadmeSync.API

/**
 * Coordinates syncing and reading of per-project roadmap TODO items.
 *
 * Primary flow:
 *   syncProjectRoadmap($project) → callApi() → ProjectRoadmapModel::upsertFromSync()
 *
 * Fallback flow (when migrate_v3.sql hasn't been run):
 *   Any DB operation that throws is caught silently and the service
 *   reads/writes app/Config/project_roadmaps.json instead.
 *
 * API contract: the ReadmeSync.API response must contain a 'todos' key.
 * If it's missing, syncProjectRoadmap() returns ok=false with an explanation.
 * See docs/readmesync-integration.md for the full contract check procedure.
 */

require_once __DIR__ . '/../Models/ProjectRoadmapModel.php';

class ProjectRoadmapService {
    private string $apiUrl;
    private string $storagePath; // JSON fallback path
    private ?ProjectRoadmapModel $roadmapModel = null;

    /**
     * @param string|null $apiUrl       Override the ReadmeSync API URL (useful in tests).
     * @param string|null $storagePath  Override the JSON fallback file path (useful in tests).
     */
    public function __construct(?string $apiUrl = null, ?string $storagePath = null) {
        $this->apiUrl      = $apiUrl ?: ((string) (getenv('READMESYNC_API_URL') ?: 'https://tombomekestudio.com/api/readmesync/generate'));
        $this->storagePath = $storagePath ?: (__DIR__ . '/../Config/project_roadmaps.json');
    }

    /**
     * Return roadmap data for a single project, reading from DB (or JSON fallback).
     *
     * Returns an array with keys: projectId, slug, title, repoUrl, lastSyncAt,
     * openCount, totalCount, items[].
     * Returns [] if the project has no id.
     *
     * @param array       $project       Project row (must contain 'id').
     * @param string|null $filterStatus  'open' | 'done' | null (no filter).
     * @param string|null $filterPriority 'high' | 'normal' | null (no filter).
     */
    public function getProjectRoadmap(array $project, ?string $filterStatus = null, ?string $filterPriority = null): array {
        $projectId = (int) ($project['id'] ?? 0);
        if ($projectId <= 0) return [];

        try {
            $model    = $this->getModel();
            $items    = $model->getByProjectId($projectId, $filterStatus, $filterPriority);
            $lastSync = $model->getLastSync($projectId);
            return [
                'projectId'  => $projectId,
                'slug'       => (string) ($project['slug']     ?? ''),
                'title'      => (string) ($project['title']    ?? ''),
                'repoUrl'    => (string) ($project['repo_url'] ?? ''),
                'lastSyncAt' => $lastSync ? $lastSync['created_at'] : null,
                'openCount'  => $model->getOpenCountByProjectId($projectId),
                'totalCount' => $model->getTotalCountByProjectId($projectId),
                'items'      => $this->normalizeItemsForView($items),
            ];
        } catch (\Throwable $e) {
            // DB not yet available — fall back to JSON
            return $this->getProjectRoadmapFromJson($project);
        }
    }

    public function getAllProjectRoadmaps(): array {
        try {
            $model   = $this->getModel();
            $grouped = $model->getAllGroupedByProject();
            // Return as ['project-{id}' => [...]] to stay compatible with existing views
            $result  = [];
            foreach ($grouped as $projectId => $data) {
                $result['project-' . $projectId] = [
                    'projectId'  => $projectId,
                    'items'      => $this->normalizeItemsForView($data['items']),
                    'openCount'  => $data['openCount'],
                    'doneCount'  => $data['doneCount'],
                    'totalCount' => $data['totalCount'],
                    'lastSyncAt' => null, // populated later by getSyncSummary if needed
                ];
            }
            return $result;
        } catch (\Throwable $e) {
            return $this->loadStore()['projects'] ?? [];
        }
    }

    /**
     * Returns per-project sync summary (lastSyncAt + counts) indexed by project_id.
     * Used by the central roadmap page to avoid N+1 sync-log queries.
     */
    public function getSyncSummary(array $projectIds): array {
        if (empty($projectIds)) return [];
        try {
            return $this->getModel()->getSyncSummaryByProjectIds($projectIds);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Returns the most recent sync log entries across all projects.
     * Used by the admin panel to show sync activity without a dedicated model import.
     */
    public function getRecentSyncLogs(int $limit = 20): array {
        try {
            return $this->getModel()->getRecentSyncLogs($limit);
        } catch (\Throwable $e) {
            return [];
        }
    }

    // TODO(roadmap): done - callApi() retries up to 3 times with exponential backoff (0/400/1200 ms) on HTTP ≥500 or transport errors; bails immediately on 4xx.
    /**
     * Trigger a full sync for one project: call the ReadmeSync API and persist results.
     *
     * Returns ['ok' => true, 'itemCount' => N, 'apiContractVersion' => '...'] on success.
     * Returns ['ok' => false, 'error' => '...'] on failure (no repo_url, API error, bad contract).
     *
     * On DB failure the data is written to the JSON fallback file instead.
     * On API failure the error is logged to project_sync_log (if DB is available).
     *
     * @param array      $project   Project row (must contain 'id' and 'repo_url').
     * @param array|null $authUser  Current admin user (passed to API for telemetry).
     */
    public function syncProjectRoadmap(array $project, ?array $authUser = null): array {
        $repoUrl = trim((string) ($project['repo_url'] ?? ''));
        if ($repoUrl === '') {
            return ['ok' => false, 'error' => 'Geen repo_url ingesteld voor dit project.'];
        }

        $response = $this->callApi($repoUrl, $authUser);
        if (!($response['ok'] ?? false)) {
            $this->logSyncFailure($project, (string) ($response['error'] ?? 'Onbekende API-fout'));
            return ['ok' => false, 'error' => (string) ($response['error'] ?? 'Onbekende API-fout')];
        }

        $todos   = (array) ($response['data']['todos'] ?? []);
        $version = (string) ($response['data']['apiContractVersion'] ?? 'unknown');
        $mapped  = [];

        foreach ($todos as $todo) {
            if (!is_array($todo)) continue;

            $text = trim((string) ($todo['text'] ?? ''));
            $status = $this->normalizeTodoStatus((string) ($todo['status'] ?? 'open'), $text);
            $priority = $this->normalizeTodoPriority((string) ($todo['priority'] ?? 'normal'), $text);

            $mapped[] = [
                'file'     => (string) ($todo['file']     ?? ''),
                'line'     => (int)    ($todo['line']     ?? 0),
                'text'     => $text,
                'status'   => $status,
                'priority' => $priority,
            ];
        }

        $projectId = (int) ($project['id'] ?? 0);
        $itemCount = 0;

        try {
            $model     = $this->getModel();
            $itemCount = $model->upsertFromSync($projectId, $mapped, $version);
            $model->logSync($projectId, $itemCount, $version, true);
        } catch (\Throwable $e) {
            // DB not available — fall back to JSON
            $itemCount = $this->syncToJson($project, $mapped, $version);
        }

        return [
            'ok'                 => true,
            'itemCount'          => $itemCount,
            'apiContractVersion' => $version,
        ];
    }

    /**
     * Call the ReadmeSync API with exponential backoff.
     * Retries up to 3 attempts (delays: 0ms, 400ms, 1200ms) on:
     *   - cURL transport errors
     *   - HTTP 500–599 (server-side errors, likely transient)
     * Bails immediately on HTTP 4xx (client errors — retrying won't help).
     */
    protected function callApi(string $repoUrl, ?array $authUser = null): array {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'error' => 'cURL is niet beschikbaar op deze server.'];
        }

        $payload = json_encode([
            'githubRepoUrl' => $repoUrl,
            'clientApp'     => 'portfolio-projects',
            'userId'        => isset($authUser['id'])       ? (string) $authUser['id']       : null,
            'userName'      => isset($authUser['username']) ? (string) $authUser['username'] : null,
        ]);

        $maxAttempts  = 3;
        $retryDelaysMs = [0, 400, 1200]; // milliseconds before each attempt
        $lastError    = 'Onbekende API-fout.';

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            if ($retryDelaysMs[$attempt] > 0) {
                usleep($retryDelaysMs[$attempt] * 1000);
            }

            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 40,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $raw      = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            // Transport error — retry
            if ($curlErr !== '') {
                $lastError = 'API niet bereikbaar: ' . $curlErr;
                continue;
            }

            // 4xx — client error, do not retry
            if ($httpCode >= 400 && $httpCode < 500) {
                return ['ok' => false, 'error' => 'API gaf HTTP ' . $httpCode];
            }

            // 5xx — server error, retry
            if ($httpCode >= 500) {
                $lastError = 'API gaf HTTP ' . $httpCode;
                continue;
            }

            // Success path
            if ($httpCode !== 200) {
                return ['ok' => false, 'error' => 'API gaf HTTP ' . $httpCode];
            }

            $decoded = json_decode((string) $raw, true);
            if (!is_array($decoded)) {
                return ['ok' => false, 'error' => 'Ongeldige JSON van API.'];
            }
            if (!array_key_exists('todos', $decoded)) {
                return ['ok' => false, 'error' => 'API response bevat geen todos key.'];
            }

            return ['ok' => true, 'data' => $decoded];
        }

        return ['ok' => false, 'error' => $lastError];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    protected function getModel(): ProjectRoadmapModel {
        if ($this->roadmapModel === null) {
            $this->roadmapModel = new ProjectRoadmapModel();
        }
        return $this->roadmapModel;
    }

    /** Normalize DB rows to the shape the views expect (camelCase keys) */
    private function normalizeItemsForView(array $items): array {
        return array_map(function (array $item) {
            $text = (string) ($item['text'] ?? '');
            $status = $this->normalizeTodoStatus((string) ($item['status'] ?? 'open'), $text);
            $priority = $this->normalizeTodoPriority((string) ($item['priority'] ?? 'normal'), $text);

            return [
                'file'                => (string) ($item['file']                ?? ''),
                'line'                => (int)    ($item['line']                ?? 0),
                'text'                => $text,
                'status'              => $status,
                'priority'            => $priority,
                'lastSeenAt'          => (string) ($item['last_seen_at']        ?? ''),
                'apiContractVersion'  => (string) ($item['api_contract_version'] ?? ''),
                'id'                  => (int)    ($item['id']                  ?? 0),
            ];
        }, $items);
    }

    private function normalizeTodoStatus(string $status, string $text): string {
        $normalized = strtolower(trim($status));
        if ($normalized === 'done' || $normalized === 'open') {
            return $normalized;
        }

        if (preg_match('/\[(done|x|fixed|resolved)\]/i', $text)) {
            return 'done';
        }

        if ($normalized === 'completed' || $normalized === 'closed') {
            return 'done';
        }

        return 'open';
    }

    private function normalizeTodoPriority(string $priority, string $text): string {
        $normalized = strtolower(trim($priority));

        if (in_array($normalized, ['high', 'critical', 'urgent', 'p1', '1'], true)) {
            return 'high';
        }
        if (in_array($normalized, ['medium', 'med', 'p2', '2'], true)) {
            return 'medium';
        }
        if (in_array($normalized, ['low', 'p3', '3'], true)) {
            return 'low';
        }

        if (preg_match('/\[P1\]/i', $text)) {
            return 'high';
        }
        if (preg_match('/\[P2\]/i', $text)) {
            return 'medium';
        }
        if (preg_match('/\[P3\]/i', $text)) {
            return 'low';
        }

        return 'normal';
    }

    private function logSyncFailure(array $project, string $error): void {
        $projectId = (int) ($project['id'] ?? 0);
        if ($projectId <= 0) return;
        try {
            $this->getModel()->logSync($projectId, 0, '', false, $error);
        } catch (\Throwable $e) {
            // ignore — logging must never crash the sync flow
        }
    }

    private function projectKey(array $project): string {
        $id = (int) ($project['id'] ?? 0);
        if ($id > 0) return 'project-' . $id;
        $slug = trim((string) ($project['slug'] ?? ''));
        if ($slug !== '') return 'slug-' . strtolower($slug);
        return 'repo-' . md5((string) ($project['repo_url'] ?? 'unknown'));
    }

    // ── JSON fallback (used when DB tables don't exist yet) ──────────────────

    private function getProjectRoadmapFromJson(array $project): array {
        $store = $this->loadStore();
        return (array) ($store['projects'][$this->projectKey($project)] ?? []);
    }

    private function syncToJson(array $project, array $items, string $version): int {
        $items = array_values(array_filter($items, fn($i) => $i['file'] !== '' || $i['text'] !== ''));
        $record = [
            'projectId'          => (int) ($project['id'] ?? 0),
            'slug'               => (string) ($project['slug'] ?? ''),
            'title'              => (string) ($project['title'] ?? ($project['title_nl'] ?? 'Project')),
            'repoUrl'            => (string) ($project['repo_url'] ?? ''),
            'apiContractVersion' => $version,
            'lastSyncAt'         => gmdate('c'),
            'items'              => array_map(fn($i) => $i + ['lastSeenAt' => gmdate('c')], $items),
        ];

        $store = $this->loadStore();
        if (!isset($store['projects']) || !is_array($store['projects'])) {
            $store['projects'] = [];
        }
        $store['projects'][$this->projectKey($project)] = $record;
        $store['updatedAt'] = gmdate('c');
        $this->saveStore($store);

        return count($record['items']);
    }

    private function loadStore(): array {
        if (!file_exists($this->storagePath)) {
            return ['updatedAt' => null, 'projects' => []];
        }
        $decoded = json_decode((string) file_get_contents($this->storagePath), true);
        return is_array($decoded) ? $decoded : ['updatedAt' => null, 'projects' => []];
    }

    private function saveStore(array $store): void {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($this->storagePath, json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
