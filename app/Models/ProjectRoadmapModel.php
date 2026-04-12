<?php
// app/Models/ProjectRoadmapModel.php — DB storage for per-project roadmap items
require_once __DIR__ . '/../Config/Database.php';

class ProjectRoadmapModel {

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

    // TODO(roadmap): preserve manually-set 'done' status across syncs instead of full DELETE+INSERT
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
}
