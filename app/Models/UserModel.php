<?php
// app/Models/UserModel.php
require_once __DIR__ . '/../Config/Database.php';

class UserModel {

    public function getAll(): array {
        $db = Database::getConnection();
        return $db->query(
            "SELECT id, username, email, role, created_at FROM users ORDER BY FIELD(role,'owner','admin'), created_at ASC"
        )->fetchAll();
    }

    public function getById(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $username, string $email, string $password, string $role = 'admin'): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)"
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role'     => in_array($role, ['owner', 'admin'], true) ? $role : 'admin',
        ]);
        return (int) $db->lastInsertId();
    }

    public function delete(int $id): void {
        // Owners can never be deleted through the UI
        $db = Database::getConnection();
        $db->prepare("DELETE FROM users WHERE id = :id AND role != 'owner'")->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function usernameExists(string $username, int $excludeId = 0): bool {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :u AND id != :id");
        $stmt->execute([':u' => $username, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function emailExists(string $email, int $excludeId = 0): bool {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :e AND id != :id");
        $stmt->execute([':e' => $email, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
