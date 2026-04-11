<?php
// app/Models/ActivityLogModel.php
require_once __DIR__ . '/../Config/Database.php';

class ActivityLogModel {

    // ── Static log helper (mirrors Laravel's ActivityLog::log()) ─────────────

    public static function log(string $action, string $description, ?string $modelType = null, ?int $modelId = null, ?array $properties = null): void {
        try {
            $db = Database::getConnection();
            $db->prepare(
                "INSERT INTO activity_logs (user_id, action, model_type, model_id, description, properties, ip_address, user_agent)
                 VALUES (:user_id, :action, :model_type, :model_id, :description, :properties, :ip, :ua)"
            )->execute([
                ':user_id'     => $_SESSION['auth_user']['id'] ?? null,
                ':action'      => $action,
                ':model_type'  => $modelType,
                ':model_id'    => $modelId,
                ':description' => $description,
                ':properties'  => $properties ? json_encode($properties) : null,
                ':ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua'          => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Silently fail if table doesn't exist yet
        }
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    public function getAll(int $limit = 50, int $offset = 0, array $filters = []): array {
        $db     = Database::getConnection();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = 'al.action = :action';
            $params[':action'] = $filters['action'];
        }
        if (!empty($filters['search'])) {
            $where[] = 'al.description LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $db->prepare(
            "SELECT al.*, u.username
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY al.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int {
        $db     = Database::getConnection();
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['action'])) { $where[] = 'action = :action'; $params[':action'] = $filters['action']; }
        if (!empty($filters['search'])) { $where[] = 'description LIKE :search'; $params[':search'] = '%' . $filters['search'] . '%'; }
        $stmt = $db->prepare("SELECT COUNT(*) FROM activity_logs WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getDistinctActions(): array {
        try {
            $db   = Database::getConnection();
            $rows = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
            return $rows ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function delete(int $id): void {
        Database::getConnection()->prepare("DELETE FROM activity_logs WHERE id = :id")->execute([':id' => $id]);
    }

    public function clearOlderThan(int $days): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // ── UI helpers ────────────────────────────────────────────────────────────

    public function getActionLabel(string $action): string {
        return match($action) {
            'created'  => 'Aangemaakt',
            'updated'  => 'Bijgewerkt',
            'deleted'  => 'Verwijderd',
            'login'    => 'Ingelogd',
            'logout'   => 'Uitgelogd',
            'approved' => 'Goedgekeurd',
            'replied'  => 'Beantwoord',
            default    => ucfirst($action),
        };
    }

    public function getActionColor(string $action): string {
        return match($action) {
            'created'  => 'success',
            'updated'  => 'primary',
            'deleted'  => 'danger',
            'login'    => 'info',
            'approved' => 'success',
            'replied'  => 'success',
            default    => '',
        };
    }

    public function getActionIcon(string $action): string {
        return match($action) {
            'created'  => 'fas fa-plus-circle',
            'updated'  => 'fas fa-pen',
            'deleted'  => 'fas fa-trash',
            'login'    => 'fas fa-sign-in-alt',
            'logout'   => 'fas fa-sign-out-alt',
            'approved' => 'fas fa-check-circle',
            'replied'  => 'fas fa-reply',
            default    => 'fas fa-circle',
        };
    }
}
