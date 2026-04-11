<?php
// app/Auth/Auth.php

class Auth {

    public static function check(): bool {
        return isset($_SESSION['auth_user']);
    }

    public static function user(): ?array {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function isOwner(): bool {
        return (self::user()['role'] ?? '') === 'owner';
    }

    public static function isAdmin(): bool {
        return in_array(self::user()['role'] ?? '', ['owner', 'admin'], true);
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            header('Location: ?page=admin&section=login');
            exit;
        }
    }

    public static function requireOwner(): void {
        self::requireAuth();
        if (!self::isOwner()) {
            header('Location: ?page=admin');
            exit;
        }
    }

    public static function login(string $username, string $password): bool {
        require_once __DIR__ . '/../Config/Database.php';
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
        return true;
    }

    public static function loginByEmail(string $email, string $password): bool {
        require_once __DIR__ . '/../Config/Database.php';
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :e LIMIT 1");
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];
        return true;
    }

    public static function logout(): void {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
