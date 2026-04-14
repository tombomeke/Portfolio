<?php
// app/Models/UserModel.php
require_once __DIR__ . '/../Config/Database.php';

class UserModel {
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 30;

    public static function normalizeUsername(string $username): string {
        return trim($username);
    }

    public static function isValidUsername(string $username): bool {
        $username = self::normalizeUsername($username);
        $pattern = '/^[a-z0-9_]{' . self::USERNAME_MIN_LENGTH . ',' . self::USERNAME_MAX_LENGTH . '}$/i';
        return (bool) preg_match($pattern, $username);
    }

    public function getAll(): array {
        $db = Database::getConnection();
        return $db->query(
            "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, created_at
             FROM users ORDER BY FIELD(role,'owner','admin','user'), created_at ASC"
        )->fetchAll();
    }

    public function getById(int $id): ?array {
        $db = Database::getConnection();
        try {
            // TODO(profile): [P3] include users.timezone in this query after timezone migration lands.
            $stmt = $db->prepare(
                "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, email_notifications, created_at
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
                "SELECT id, username, email, role, birthday, profile_photo_path, about, public_profile, preferred_language, email_notifications, created_at
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

    public function updateSettings(int $id, string $preferredLanguage, bool $publicProfile, bool $emailNotifications): void {
        // TODO(profile): [P3] extend this update with timezone column once users.timezone migration is added.
        Database::getConnection()->prepare(
            "UPDATE users
             SET preferred_language = :preferred_language,
                 public_profile = :public_profile,
                 email_notifications = :email_notifications,
                 updated_at = NOW()
             WHERE id = :id"
        )->execute([
            ':preferred_language' => $preferredLanguage === 'en' ? 'en' : 'nl',
            ':public_profile' => $publicProfile ? 1 : 0,
            ':email_notifications' => $emailNotifications ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function updatePassword(int $id, string $password): void {
        Database::getConnection()->prepare(
            "UPDATE users SET password=:pass, updated_at=NOW() WHERE id=:id"
        )->execute([':pass' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]);
    }

    /**
     * Verify a plain-text password against the stored hash for a given user.
     * Use before allowing sensitive settings updates (password change, email change).
     * Returns false if the user is not found.
     */
    public function verifyPassword(int $id, string $plainPassword): bool {
        $stmt = Database::getConnection()->prepare(
            "SELECT password FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) && $hash !== '' && password_verify($plainPassword, $hash);
    }

    public function updateRole(int $id, string $role): void {
        $allowed = ['owner', 'admin', 'user'];
        if (!in_array($role, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid role provided.');
        }

        Database::getConnection()->prepare(
            "UPDATE users SET role=:role, updated_at=NOW() WHERE id=:id"
        )->execute([':role' => $role, ':id' => $id]);
    }

    public function create(string $username, string $email, string $password, string $role = 'user'): int {
        $username = self::normalizeUsername($username);
        if (!self::isValidUsername($username)) {
            throw new \InvalidArgumentException('Invalid username format.');
        }

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
        if (empty($base) || strlen($base) < self::USERNAME_MIN_LENGTH) {
            $base = 'user';
        }
        $base = substr($base, 0, self::USERNAME_MAX_LENGTH);

        $username = $base;
        $counter  = 1;
        while ($this->usernameExists($username)) {
            $suffix = (string) $counter;
            $prefixLength = max(1, self::USERNAME_MAX_LENGTH - strlen($suffix));
            $username = substr($base, 0, $prefixLength) . $suffix;
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

    public function countAdminUsers(): int {
        $stmt = Database::getConnection()->query("SELECT COUNT(*) FROM users WHERE role IN ('owner', 'admin')");
        return (int) $stmt->fetchColumn();
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
