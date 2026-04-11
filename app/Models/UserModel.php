<?php
// app/Models/UserModel.php
require_once __DIR__ . '/../Config/Database.php';

class UserModel {

    public function getAll(): array {
        $db = Database::getConnection();
        return $db->query(
            "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, created_at
             FROM users ORDER BY FIELD(role,'owner','admin'), created_at ASC"
        )->fetchAll();
    }

    public function getById(int $id): ?array {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, created_at
                 FROM users WHERE id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
        }
        return $stmt->fetch() ?: null;
    }

    public function getByUsername(string $username): ?array {
        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, created_at
                 FROM users WHERE username = :u LIMIT 1"
            );
            $stmt->execute([':u' => $username]);
        } catch (\PDOException $e) {
            // Fallback: profile columns don't exist yet (migrate_v2.sql not run)
            $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
        }
        return $stmt->fetch() ?: null;
    }

    public function updateProfile(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE users SET about=:about, birthday=:birthday, public_profile=:public_profile,
             preferred_language=:preferred_language, updated_at=NOW() WHERE id=:id"
        )->execute([
            ':about'              => $data['about']              ?? null,
            ':birthday'           => $data['birthday']           ?: null,
            ':public_profile'     => (int) ($data['public_profile'] ?? 1),
            ':preferred_language' => $data['preferred_language'] ?? 'nl',
            ':id'                 => $id,
        ]);
    }

    public function updateProfilePhoto(int $id, string $path): void {
        Database::getConnection()->prepare(
            "UPDATE users SET profile_photo_path=:path, updated_at=NOW() WHERE id=:id"
        )->execute([':path' => $path, ':id' => $id]);
    }

    public function updatePassword(int $id, string $password): void {
        Database::getConnection()->prepare(
            "UPDATE users SET password=:pass, updated_at=NOW() WHERE id=:id"
        )->execute([':pass' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
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
            ':role'     => in_array($role, ['owner', 'admin', 'user'], true) ? $role : 'admin',
        ]);
        return (int) $db->lastInsertId();
    }

    public function createPublicUser(string $name, string $email, string $password): int {
        $username = $this->generateUsername($name);
        return $this->create($username, $email, $password, 'user');
    }

    public function generateUsername(string $name): string {
        // Lowercase, keep alphanumerics, replace spaces/special chars with nothing
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        if (empty($base)) {
            $base = 'user';
        }
        $base = substr($base, 0, 20);

        $username = $base;
        $counter  = 1;
        while ($this->usernameExists($username)) {
            $username = $base . $counter;
            $counter++;
        }
        return $username;
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
