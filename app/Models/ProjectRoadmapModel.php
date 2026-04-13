<?php
// app/Models/ProjectRoadmapModel.php — DB storage for per-project roadmap items

/**
 * Manages roadmap TODO items that are synced from ReadmeSync.API.
 *
 * Two tables:
 *   - project_roadmap_items  — one row per TODO item (file, line, text, status, priority)
 *   - project_sync_log       — one row per sync attempt (success/failure + item count)
 *
 * Items are fully replaced on each sync (DELETE + INSERT). See upsertFromSync().
 * The sync log is append-only; use getLastSync() to surface the most recent attempt.
 *
 * Requires migrate_v3.sql to have been run. If not, ProjectRoadmapService falls back to JSON.
 */

require_once __DIR__ . '/../Config/Database.php';

class ProjectRoadmapModel {

    /**
     * Fetch roadmap items for a project, with optional status and priority filters.
     * Results are ordered: high-priority first, then by insertion order.
     *
     * @param int         $projectId
     * @param string|null $status   'open' | 'done' | null (all)
     * @param string|null $priority 'high' | 'normal' | null (all)
     */
    public function getByProjectId(int $projectId, ?string $status = null, ?string $priority = null): array {
        $db     = Database::getConnection();
        $where  = ['project_id = :project_id'];
        $params = [':project_id' => $projectId];

        if ($status !== null && $status !== '') {
            $where[]           = 'status = :status';
            $params[':status'] = $status;
        }
        if ($priority !== null && $priority !== '') {
            $where[]             = 'priority = :priority';
            $params[':priority'] = $priority;
        }

        $sql  = 'SELECT * FROM project_roadmap_items WHERE ' . implode(' AND ', $where)
              . ' ORDER BY priority = "high" DESC, id ASC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getOpenCountByProjectId(int $projectId): int {
        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) FROM project_roadmap_items WHERE project_id = :id AND status != 'done'"
        );
        $stmt->execute([':id' => $projectId]);
        return (int) $stmt->fetchColumn();
    }

    public function getDoneCountByProjectId(int $projectId): int {
        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) FROM project_roadmap_items WHERE project_id = :id AND status = 'done'"
        );
        $stmt->execute([':id' => $projectId]);
        return (int) $stmt->fetchColumn();
    }

    public function getTotalCountByProjectId(int $projectId): int {
        $stmt = Database::getConnection()->prepare(
            "SELECT COUNT(*) FROM project_roadmap_items WHERE project_id = :id"
        );
        $stmt->execute([':id' => $projectId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Returns all items grouped by project_id as ['projectId' => ['items' => [...], 'openCount' => N, ...]]
     */
    public function getAllGroupedByProject(): array {
        $stmt = Database::getConnection()->query(
            "SELECT * FROM project_roadmap_items ORDER BY project_id ASC, priority = 'high' DESC, id ASC"
        );
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $pid = (int) $row['project_id'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = ['items' => [], 'openCount' => 0, 'doneCount' => 0, 'totalCount' => 0];
            }
            $grouped[$pid]['items'][] = $row;
            $grouped[$pid]['totalCount']++;
            if ($row['status'] !== 'done') {
                $grouped[$pid]['openCount']++;
            } else {
                $grouped[$pid]['doneCount']++;
            }
        }
        return $grouped;
    }

    /**
     * Returns roadmap summary metrics for a list of projects in one aggregated query.
     *
     * @param array<int> $projectIds
     * @return array<int, array{lastSyncAt:?string, openCount:int, doneCount:int, totalCount:int}>
     */
    public function getSyncSummaryByProjectIds(array $projectIds): array {
        $projectIds = array_values(array_unique(array_map('intval', $projectIds)));
        $projectIds = array_values(array_filter($projectIds, static fn (int $id): bool => $id > 0));
        if (empty($projectIds)) {
            return [];
        }

        $db = Database::getConnection();
        $placeholderList = implode(', ', array_fill(0, count($projectIds), '?'));

        $sql = "SELECT p.id AS project_id,
                       sync.last_sync_at,
                       COALESCE(items.open_count, 0) AS open_count,
                       COALESCE(items.done_count, 0) AS done_count,
                       COALESCE(items.total_count, 0) AS total_count
                  FROM projects p
                  LEFT JOIN (
                        SELECT project_id, MAX(created_at) AS last_sync_at
                          FROM project_sync_log
                         WHERE project_id IN ($placeholderList)
                         GROUP BY project_id
                  ) sync ON sync.project_id = p.id
                  LEFT JOIN (
                        SELECT project_id,
                               SUM(CASE WHEN status = 'done' THEN 0 ELSE 1 END) AS open_count,
                               SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done_count,
                               COUNT(*) AS total_count
                          FROM project_roadmap_items
                         WHERE project_id IN ($placeholderList)
                         GROUP BY project_id
                  ) items ON items.project_id = p.id
                 WHERE p.id IN ($placeholderList)";

        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($projectIds, $projectIds, $projectIds));

        $summary = [];
        foreach ($stmt->fetchAll() as $row) {
            $projectId = (int) ($row['project_id'] ?? 0);
            if ($projectId <= 0) {
                continue;
            }

            $summary[$projectId] = [
                'lastSyncAt' => $row['last_sync_at'] !== null ? (string) $row['last_sync_at'] : null,
                'openCount'  => (int) ($row['open_count'] ?? 0),
                'doneCount'  => (int) ($row['done_count'] ?? 0),
                'totalCount' => (int) ($row['total_count'] ?? 0),
            ];
        }

        return $summary;
    }

    // TODO(roadmap): [P2] preserve manually-set 'done' status across syncs instead of full DELETE+INSERT
    // TODO(roadmap): [P2] add diff support — store which items are new vs. removed since last sync
    // so the UI can surface a "new since last sync" badge per item.
    /**
     * Replace all items for this project with a fresh set from sync.
     * Returns the number of items inserted.
     */
    public function upsertFromSync(int $projectId, array $items, string $apiContractVersion): int {
        $db = Database::getConnection();
        $db->prepare("DELETE FROM project_roadmap_items WHERE project_id = :id")->execute([':id' => $projectId]);

        $stmt = $db->prepare(
            "INSERT INTO project_roadmap_items
                (project_id, file, line, text, status, priority, last_seen_at, api_contract_version)
             VALUES
                (:project_id, :file, :line, :text, :status, :priority, :last_seen_at, :api_contract_version)"
        );

        $now   = gmdate('Y-m-d H:i:s');
        $count = 0;
        foreach ($items as $item) {
            $file = (string) ($item['file'] ?? '');
            $text = trim((string) ($item['text'] ?? ''));
            if ($file === '' && $text === '') continue;

            $stmt->execute([
                ':project_id'           => $projectId,
                ':file'                 => $file,
                ':line'                 => (int) ($item['line'] ?? 0),
                ':text'                 => $text,
                ':status'               => (string) ($item['status']   ?? 'open'),
                ':priority'             => (string) ($item['priority'] ?? 'normal'),
                ':last_seen_at'         => $now,
                ':api_contract_version' => $apiContractVersion,
            ]);
            $count++;
        }
        return $count;
    }

    public function logSync(int $projectId, int $itemCount, string $apiContractVersion, bool $success, ?string $error = null): void {
        Database::getConnection()->prepare(
            "INSERT INTO project_sync_log (project_id, item_count, api_contract_version, success, error_message)
             VALUES (:project_id, :item_count, :api_contract_version, :success, :error_message)"
        )->execute([
            ':project_id'           => $projectId,
            ':item_count'           => $itemCount,
            ':api_contract_version' => $apiContractVersion,
            ':success'              => $success ? 1 : 0,
            ':error_message'        => $error,
        ]);
    }

    public function getLastSync(int $projectId): ?array {
        $stmt = Database::getConnection()->prepare(
            "SELECT * FROM project_sync_log WHERE project_id = :id ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([':id' => $projectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Returns the most recent sync log entries across all projects, joined with project title.
     * Used on the admin projects page to give an overview of recent sync activity.
     */
    public function getRecentSyncLogs(int $limit = 20): array {
        $stmt = Database::getConnection()->prepare(
            "SELECT l.*, p.title_nl AS project_title, p.slug AS project_slug
               FROM project_sync_log l
               LEFT JOIN projects p ON p.id = l.project_id
              ORDER BY l.created_at DESC
              LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
