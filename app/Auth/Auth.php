<?php
// app/Auth/Auth.php

/**
 * Session-based authentication helper.
 *
 * Two roles exist:
 *   - 'owner' — full access (can manage users, settings, all content)
 *   - 'admin' — content management (news, FAQ, projects, contact)
 *
 * The session key $_SESSION['auth_user'] holds a minimal user snapshot
 * built by makeSessionUser(). It is refreshed per request through index.php
 * and protected auth checks so role/profile changes become visible quickly.
 *
 * CSRF tokens are stored in $_SESSION['csrf_token'] and verified on every
 * state-changing POST. Use csrfField() in forms and verifyCsrf() in handlers.
 */
class Auth {

    /**
     * Build the session payload from a full DB user row.
     * Only stores the fields the layout/navbar actually needs.
     */
    private static function makeSessionUser(array $user): array {
        return [
            'id'                 => $user['id'],
            'username'           => $user['username'],
            'email'              => $user['email'],
            'role'               => $user['role'],
            'profile_photo_path' => $user['profile_photo_path'] ?? null,
            'preferred_language' => $user['preferred_language'] ?? 'nl',
        ];
    }

    /** Returns true if a user is logged in (session key exists). */
    public static function check(): bool {
        return isset($_SESSION['auth_user']);
    }

    /**
     * Refresh the session user snapshot from the database.
     * This keeps role/profile fields in sync after promotions/demotions/profile edits.
     */
    public static function refreshSession(): void {
        if (!self::check()) {
            return;
        }

        $sessionUser = self::user();
        $userId = (int) ($sessionUser['id'] ?? 0);
        if ($userId <= 0) {
            self::logout();
            return;
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $stmt = Database::getConnection()->prepare(
                "SELECT id, username, email, role, profile_photo_path, preferred_language
                 FROM users WHERE id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $userId]);
            $freshUser = $stmt->fetch();
        } catch (\Throwable $e) {
            // Keep existing session snapshot when DB is temporarily unavailable.
            return;
        }

        if (!$freshUser) {
            self::logout();
            return;
        }

        $_SESSION['auth_user'] = self::makeSessionUser($freshUser);
    }

    /** Returns the current session user array, or null if not logged in. */
    public static function user(): ?array {
        return $_SESSION['auth_user'] ?? null;
    }

    /** Returns true only for role=owner. */
    public static function isOwner(): bool {
        return (self::user()['role'] ?? '') === 'owner';
    }

    /** Returns true for both role=owner and role=admin. */
    public static function isAdmin(): bool {
        return in_array(self::user()['role'] ?? '', ['owner', 'admin'], true);
    }

    /** Redirect to login page if no session. Call at the top of every protected handler. */
    public static function requireAuth(): void {
        if (!self::check()) {
            header('Location: ?page=admin&section=login');
            exit;
        }

        self::refreshSession();

        if (!self::check()) {
            header('Location: ?page=admin&section=login');
            exit;
        }
    }

    /** Redirect to admin dashboard if logged in but not owner. */
    public static function requireOwner(): void {
        self::requireAuth();
        if (!self::isOwner()) {
            header('Location: ?page=admin');
            exit;
        }
    }

    // TODO(auth): done - Enforce admin/owner-only access for admin routes.
    /** Redirect to home page if logged in but not owner/admin. */
    public static function requireAdmin(): void {
        self::requireAuth();
        if (!self::isAdmin()) {
            header('Location: ?page=home');
            exit;
        }
    }

    /**
     * Attempt login by username + password.
     * Regenerates the session ID on success to prevent session fixation.
     * Returns true on success, false on wrong credentials.
     */
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
        $_SESSION['auth_user'] = self::makeSessionUser($user);
        return true;
    }

    /**
     * Attempt login by email + password (alternative to username login).
     * Same session-regeneration behaviour as login().
     */
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
        $_SESSION['auth_user'] = self::makeSessionUser($user);
        return true;
    }

    /** Store a DB user row in session using the canonical auth payload shape. */
    public static function setSessionUser(array $user): void {
        $_SESSION['auth_user'] = self::makeSessionUser($user);
    }

    /** Destroy the auth session and regenerate the session ID. */
    public static function logout(): void {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }

    /**
     * Return the current CSRF token, generating one if it doesn't exist yet.
     * Token is 64 hex characters (32 random bytes).
     */
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Constant-time comparison to verify a submitted CSRF token.
     * Always returns false if the session token is missing.
     */
    public static function verifyCsrf(string $token): bool {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Rotate the CSRF token. Call after a successful sensitive POST action
     * (password change, profile update, security settings) so the old token
     * cannot be replayed.
     * TODO(csrf): done - rotateCsrf() implemented; wire into each sensitive handler.
     */
    public static function rotateCsrf(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /** Render a hidden <input> with the current CSRF token. Use inside every POST form. */
    public static function csrfField(): string {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
