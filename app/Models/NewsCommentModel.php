<?php
// app/Models/NewsCommentModel.php
require_once __DIR__ . '/../Config/Database.php';

class NewsCommentModel {

    // ── Public ────────────────────────────────────────────────────────────────

    public function getForNewsItem(int $newsItemId, bool $approvedOnly = true): array {
        $db  = Database::getConnection();
        $sql = "SELECT c.*, u.username
                FROM news_comments c
                LEFT JOIN users u ON u.id = c.user_id
                WHERE c.news_item_id = :id";
        if ($approvedOnly) $sql .= " AND c.is_approved = 1";
        $sql .= " ORDER BY c.created_at ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $newsItemId]);
        return $stmt->fetchAll();
    }

    public function create(int $newsItemId, int $userId, string $body, bool $isApproved = false): int {
        $db = Database::getConnection();
        $db->prepare(
            "INSERT INTO news_comments (news_item_id, user_id, body, is_approved, approved_at)
             VALUES (:news_id, :user_id, :body, :approved, :approved_at)"
        )->execute([
            ':news_id'     => $newsItemId,
            ':user_id'     => $userId,
            ':body'        => $body,
            ':approved'    => $isApproved ? 1 : 0,
            ':approved_at' => $isApproved ? date('Y-m-d H:i:s') : null,
        ]);
        return (int) $db->lastInsertId();
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function getAllForAdmin(int $limit = 50, int $offset = 0): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT c.*, u.username,
                    COALESCE(nl.title, en.title) AS news_title,
                    n.id AS news_id
             FROM news_comments c
             LEFT JOIN users u              ON u.id = c.user_id
             LEFT JOIN news_items n         ON n.id = c.news_item_id
             LEFT JOIN news_item_translations nl ON nl.news_item_id = n.id AND nl.lang = 'nl'
             LEFT JOIN news_item_translations en ON en.news_item_id = n.id AND en.lang = 'en'
             ORDER BY c.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT c.*, u.username FROM news_comments c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE c.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function approve(int $id): void {
        Database::getConnection()->prepare(
            "UPDATE news_comments SET is_approved=1, approved_at=NOW() WHERE id=:id"
        )->execute([':id' => $id]);
    }

    public function delete(int $id): void {
        Database::getConnection()->prepare("DELETE FROM news_comments WHERE id=:id")->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM news_comments")->fetchColumn();
    }

    public function countPending(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM news_comments WHERE is_approved=0")->fetchColumn();
    }
}
