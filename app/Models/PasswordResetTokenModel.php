<?php
require_once __DIR__ . '/../Config/Database.php';

class PasswordResetTokenModel {
    public function createForUser(int $userId, string $email, int $ttlMinutes = 60): array {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->add(new DateInterval('PT' . max(1, $ttlMinutes) . 'M'))
            ->format('Y-m-d H:i:s');

        $db = Database::getConnection();
        $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = :user_id AND email = :email AND used_at IS NULL")
            ->execute([':user_id' => $userId, ':email' => $email]);

        $db->prepare(
            "INSERT INTO password_reset_tokens (user_id, email, token_hash, expires_at)
             VALUES (:user_id, :email, :token_hash, :expires_at)"
        )->execute([
            ':user_id' => $userId,
            ':email' => $email,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    public function findValidToken(string $token): ?array {
        $tokenHash = hash('sha256', $token);
        $stmt = Database::getConnection()->prepare(
            "SELECT id, user_id, email, expires_at, used_at
             FROM password_reset_tokens
             WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > UTC_TIMESTAMP()
             LIMIT 1"
        );
        $stmt->execute([':token_hash' => $tokenHash]);
        return $stmt->fetch() ?: null;
    }

    public function consume(int $id): void {
        Database::getConnection()->prepare(
            "UPDATE password_reset_tokens SET used_at = UTC_TIMESTAMP() WHERE id = :id AND used_at IS NULL"
        )->execute([':id' => $id]);
    }
}