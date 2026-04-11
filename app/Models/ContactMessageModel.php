<?php
// app/Models/ContactMessageModel.php
require_once __DIR__ . '/../Config/Database.php';

class ContactMessageModel {

    public function save(string $name, string $email, string $message, ?string $subject = null): bool {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO contact_messages (name, email, subject, message, ip_address, user_agent)
             VALUES (:name, :email, :subject, :message, :ip, :ua)"
        );
        return $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    }

    public function getAll(int $limit = 50, int $offset = 0): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function markRead(int $id): void {
        $db = Database::getConnection();
        $db->prepare("UPDATE contact_messages SET read_at = NOW() WHERE id = :id AND read_at IS NULL")
           ->execute([':id' => $id]);
    }

    public function saveReply(int $id, string $reply): void {
        $db = Database::getConnection();
        $db->prepare("UPDATE contact_messages SET admin_reply = :reply, replied_at = NOW() WHERE id = :id")
           ->execute([':reply' => $reply, ':id' => $id]);
    }

    public function delete(int $id): void {
        Database::getConnection()->prepare("DELETE FROM contact_messages WHERE id = :id")->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
    }

    public function countUnread(): int {
        return (int) Database::getConnection()->query(
            "SELECT COUNT(*) FROM contact_messages WHERE read_at IS NULL"
        )->fetchColumn();
    }
}
