<?php
require_once __DIR__ . '/../Config/Database.php';

class UserSkillModel {
    private ?bool $tableExists = null;

    private function hasTable(): bool {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }

        try {
            $stmt = Database::getConnection()->query("SHOW TABLES LIKE 'user_skills'");
            $this->tableExists = (bool) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            $this->tableExists = false;
        }

        return $this->tableExists;
    }

    public function getPublicByUserId(int $userId): array {
        if (!$this->hasTable()) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT id, name, category, level, years_experience, is_public, created_at
             FROM user_skills
             WHERE user_id = :user_id AND is_public = 1
             ORDER BY level DESC, name ASC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId): array {
        if (!$this->hasTable()) {
            return [];
        }

        $stmt = Database::getConnection()->prepare(
            "SELECT id, name, category, level, years_experience, is_public, created_at
             FROM user_skills
             WHERE user_id = :user_id
             ORDER BY level DESC, name ASC"
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $name, string $category, int $level, ?int $yearsExperience, bool $isPublic): int {
        if (!$this->hasTable()) {
            return 0;
        }

        $stmt = Database::getConnection()->prepare(
            "INSERT INTO user_skills (user_id, name, category, level, years_experience, is_public)
             VALUES (:user_id, :name, :category, :level, :years_experience, :is_public)"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':name' => trim($name),
            ':category' => trim($category),
            ':level' => max(1, min(5, $level)),
            ':years_experience' => $yearsExperience,
            ':is_public' => $isPublic ? 1 : 0,
        ]);
        return (int) Database::getConnection()->lastInsertId();
    }

    public function updateForUser(int $skillId, int $userId, string $name, string $category, int $level, ?int $yearsExperience, bool $isPublic): void {
        if (!$this->hasTable()) {
            return;
        }

        $stmt = Database::getConnection()->prepare(
            "UPDATE user_skills
             SET name = :name,
                 category = :category,
                 level = :level,
                 years_experience = :years_experience,
                 is_public = :is_public,
                 updated_at = NOW()
             WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            ':id' => $skillId,
            ':user_id' => $userId,
            ':name' => trim($name),
            ':category' => trim($category),
            ':level' => max(1, min(5, $level)),
            ':years_experience' => $yearsExperience,
            ':is_public' => $isPublic ? 1 : 0,
        ]);
    }

    public function deleteForUser(int $skillId, int $userId): void {
        if (!$this->hasTable()) {
            return;
        }

        $stmt = Database::getConnection()->prepare(
            "DELETE FROM user_skills WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([':id' => $skillId, ':user_id' => $userId]);
    }
}
