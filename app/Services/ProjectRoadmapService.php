<?php
// app/Services/ProjectRoadmapService.php — sync roadmap TODOs from ReadmeSync.API
require_once __DIR__ . '/../Models/ProjectRoadmapModel.php';

class ProjectRoadmapService {
    private string $apiUrl;
    private string $storagePath; // JSON fallback path
    private ?ProjectRoadmapModel $roadmapModel = null;

    public function __construct(?string $apiUrl = null, ?string $storagePath = null) {
        $this->apiUrl      = $apiUrl ?: ((string) (getenv('READMESYNC_API_URL') ?: 'https://tombomekestudio.com/api/readmesync/generate'));
        $this->storagePath = $storagePath ?: (__DIR__ . '/../Config/project_roadmaps.json');
    }

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
            $model   = $this->getModel();
            $summary = [];
            foreach ($projectIds as $id) {
                $id         = (int) $id;
                $lastSync   = $model->getLastSync($id);
                $summary[$id] = [
                    'lastSyncAt'  => $lastSync ? $lastSync['created_at'] : null,
                    'openCount'   => $model->getOpenCountByProjectId($id),
                    'doneCount'   => $model->getDoneCountByProjectId($id),
                    'totalCount'  => $model->getTotalCountByProjectId($id),
                ];
            }
            return $summary;
        } catch (\Throwable $e) {
            return [];
        }
    }

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
            $mapped[] = [
                'file'     => (string) ($todo['file']     ?? ''),
                'line'     => (int)    ($todo['line']     ?? 0),
                'text'     => trim((string) ($todo['text'] ?? '')),
                'status'   => (string) ($todo['status']   ?? 'open'),
                'priority' => (string) ($todo['priority'] ?? 'normal'),
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

        if ($curlErr !== '') {
            return ['ok' => false, 'error' => 'API niet bereikbaar: ' . $curlErr];
        }
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getModel(): ProjectRoadmapModel {
        if ($this->roadmapModel === null) {
            $this->roadmapModel = new ProjectRoadmapModel();
        }
        return $this->roadmapModel;
    }

    /** Normalize DB rows to the shape the views expect (camelCase keys) */
    private function normalizeItemsForView(array $items): array {
        return array_map(function (array $item) {
            return [
                'file'                => (string) ($item['file']                ?? ''),
                'line'                => (int)    ($item['line']                ?? 0),
                'text'                => (string) ($item['text']                ?? ''),
                'status'              => (string) ($item['status']              ?? 'open'),
                'priority'            => (string) ($item['priority']            ?? 'normal'),
                'lastSeenAt'          => (string) ($item['last_seen_at']        ?? ''),
                'apiContractVersion'  => (string) ($item['api_contract_version'] ?? ''),
                'id'                  => (int)    ($item['id']                  ?? 0),
            ];
        }, $items);
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
