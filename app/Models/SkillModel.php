<?php
// app/Models/SkillModel.php — DB-driven (migrated from static array)
require_once __DIR__ . '/../Config/Database.php';

class SkillModel {

    // ── Public-facing ────────────────────────────────────────────────────────

    public function getAllSkills(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT id, name, category, level, notes, projects
             FROM skills ORDER BY sort_order ASC, id ASC"
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['projects'] = json_decode($row['projects'] ?? '[]', true) ?? [];
        }
        return $rows;
    }

    public function getSkillsByCategory(string $category): array {
        return array_values(
            array_filter($this->getAllSkills(), fn($s) => $s['category'] === $category)
        );
    }

    public function getEducation(string $lang = 'nl'): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT e.id, e.sort_order, e.certificate_url,
                    t.title, t.institution, t.period, t.description, t.skills_list
             FROM   education_items e
             JOIN   education_item_translations t ON t.education_item_id = e.id AND t.lang = :lang
             ORDER  BY e.sort_order ASC, e.id ASC"
        );
        $stmt->execute([':lang' => $lang]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['skills_list'] = json_decode($row['skills_list'] ?? '[]', true) ?? [];
            // Normalize to modal-ready structure
            $row = $this->normalizeEducationRow($row);
        }
        return $rows;
    }

    public function getLearningGoals(string $lang = 'nl'): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT g.id, g.progress, g.sort_order,
                    t.title, t.description, t.timeline, t.resources
             FROM   learning_goals g
             JOIN   learning_goal_translations t ON t.learning_goal_id = g.id AND t.lang = :lang
             ORDER  BY g.sort_order ASC, g.id ASC"
        );
        $stmt->execute([':lang' => $lang]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['resources'] = json_decode($row['resources'] ?? '[]', true) ?? [];
            $row = $this->normalizeLearningRow($row);
        }
        return $rows;
    }

    public function getLevelText(int $level): string {
        switch ($level) {
            case 1: return trans('skills_level_beginner');
            case 2: return trans('skills_level_intermediate');
            case 3: return trans('skills_level_advanced');
            default: return 'Unknown';
        }
    }

    public function getLevelPercentage(int $level): float {
        return ($level / 3) * 100;
    }

    public function getModalData(array $skill): string {
        return htmlspecialchars(json_encode($skill), ENT_QUOTES, 'UTF-8');
    }

    public function buildEducationModalData(array $item, int $index): string {
        return htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
    }

    public function buildLearningModalData(array $item, int $index): string {
        return htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8');
    }

    // ── Admin: Skills ─────────────────────────────────────────────────────────

    public function getAllSkillsForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT * FROM skills ORDER BY sort_order ASC, id ASC"
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['projects'] = json_decode($row['projects'] ?? '[]', true) ?? [];
        }
        return $rows;
    }

    public function getSkillByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM skills WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['projects'] = json_decode($row['projects'] ?? '[]', true) ?? [];
        return $row;
    }

    public function createSkill(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO skills (name, category, level, notes, projects, sort_order)
             VALUES (:name, :category, :level, :notes, :projects, :sort_order)"
        );
        $stmt->execute([
            ':name'       => $data['name'],
            ':category'   => $data['category'],
            ':level'      => (int) $data['level'],
            ':notes'      => $data['notes'] ?? null,
            ':projects'   => json_encode($data['projects'] ?? []),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $db->lastInsertId();
    }

    public function updateSkill(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE skills SET name=:name, category=:category, level=:level,
             notes=:notes, projects=:projects, sort_order=:sort_order, updated_at=NOW()
             WHERE id=:id"
        )->execute([
            ':name'       => $data['name'],
            ':category'   => $data['category'],
            ':level'      => (int) $data['level'],
            ':notes'      => $data['notes'] ?? null,
            ':projects'   => json_encode($data['projects'] ?? []),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
            ':id'         => $id,
        ]);
    }

    public function deleteSkill(int $id): void {
        Database::getConnection()->prepare("DELETE FROM skills WHERE id = :id")->execute([':id' => $id]);
    }

    public function countSkills(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    }

    // ── Admin: Education ──────────────────────────────────────────────────────

    public function getAllEducationForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT e.*,
                    nl.title AS title_nl, nl.institution AS institution_nl, nl.period AS period_nl,
                    nl.description AS description_nl, nl.skills_list AS skills_nl,
                    en.title AS title_en, en.institution AS institution_en, en.period AS period_en,
                    en.description AS description_en, en.skills_list AS skills_en
             FROM education_items e
             LEFT JOIN education_item_translations nl ON nl.education_item_id = e.id AND nl.lang = 'nl'
             LEFT JOIN education_item_translations en ON en.education_item_id = e.id AND en.lang = 'en'
             ORDER BY e.sort_order ASC, e.id ASC"
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['skills_nl'] = json_decode($row['skills_nl'] ?? '[]', true) ?? [];
            $row['skills_en'] = json_decode($row['skills_en'] ?? '[]', true) ?? [];
        }
        return $rows;
    }

    public function getEducationByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT e.*,
                    nl.title AS title_nl, nl.institution AS institution_nl, nl.period AS period_nl,
                    nl.description AS description_nl, nl.skills_list AS skills_nl,
                    en.title AS title_en, en.institution AS institution_en, en.period AS period_en,
                    en.description AS description_en, en.skills_list AS skills_en
             FROM education_items e
             LEFT JOIN education_item_translations nl ON nl.education_item_id = e.id AND nl.lang = 'nl'
             LEFT JOIN education_item_translations en ON en.education_item_id = e.id AND en.lang = 'en'
             WHERE e.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['skills_nl'] = json_decode($row['skills_nl'] ?? '[]', true) ?? [];
        $row['skills_en'] = json_decode($row['skills_en'] ?? '[]', true) ?? [];
        return $row;
    }

    public function createEducation(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO education_items (sort_order, certificate_url) VALUES (:sort_order, :cert)"
        );
        $stmt->execute([':sort_order' => (int)($data['sort_order']??0), ':cert' => $data['certificate_url']??null]);
        $id = (int) $db->lastInsertId();
        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO education_item_translations (education_item_id, lang, title, institution, period, description, skills_list)
                 VALUES (:id, :lang, :title, :institution, :period, :description, :skills)"
            )->execute([
                ':id'          => $id,
                ':lang'        => $lang,
                ':title'       => $data["title_{$lang}"] ?? '',
                ':institution' => $data["institution_{$lang}"] ?? null,
                ':period'      => $data["period_{$lang}"] ?? null,
                ':description' => $data["description_{$lang}"] ?? null,
                ':skills'      => json_encode(array_values(array_filter(array_map('trim', explode("\n", $data["skills_{$lang}"] ?? ''))))),
            ]);
        }
        return $id;
    }

    public function updateEducation(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare("UPDATE education_items SET sort_order=:s, certificate_url=:c, updated_at=NOW() WHERE id=:id")
           ->execute([':s' => (int)($data['sort_order']??0), ':c' => $data['certificate_url']??null, ':id' => $id]);
        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO education_item_translations (education_item_id, lang, title, institution, period, description, skills_list)
                 VALUES (:id, :lang, :title, :institution, :period, :description, :skills)
                 ON DUPLICATE KEY UPDATE title=VALUES(title), institution=VALUES(institution),
                   period=VALUES(period), description=VALUES(description), skills_list=VALUES(skills_list)"
            )->execute([
                ':id'          => $id,
                ':lang'        => $lang,
                ':title'       => $data["title_{$lang}"] ?? '',
                ':institution' => $data["institution_{$lang}"] ?? null,
                ':period'      => $data["period_{$lang}"] ?? null,
                ':description' => $data["description_{$lang}"] ?? null,
                ':skills'      => json_encode(array_values(array_filter(array_map('trim', explode("\n", $data["skills_{$lang}"] ?? ''))))),
            ]);
        }
    }

    public function deleteEducation(int $id): void {
        Database::getConnection()->prepare("DELETE FROM education_items WHERE id = :id")->execute([':id' => $id]);
    }

    // ── Admin: Learning goals ─────────────────────────────────────────────────

    public function getAllGoalsForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT g.*,
                    nl.title AS title_nl, nl.description AS description_nl, nl.timeline AS timeline_nl, nl.resources AS resources_nl,
                    en.title AS title_en, en.description AS description_en, en.timeline AS timeline_en, en.resources AS resources_en
             FROM learning_goals g
             LEFT JOIN learning_goal_translations nl ON nl.learning_goal_id = g.id AND nl.lang = 'nl'
             LEFT JOIN learning_goal_translations en ON en.learning_goal_id = g.id AND en.lang = 'en'
             ORDER BY g.sort_order ASC, g.id ASC"
        );
        return $stmt->fetchAll();
    }

    public function getGoalByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT g.*,
                    nl.title AS title_nl, nl.description AS description_nl, nl.timeline AS timeline_nl, nl.resources AS resources_nl,
                    en.title AS title_en, en.description AS description_en, en.timeline AS timeline_en, en.resources AS resources_en
             FROM learning_goals g
             LEFT JOIN learning_goal_translations nl ON nl.learning_goal_id = g.id AND nl.lang = 'nl'
             LEFT JOIN learning_goal_translations en ON en.learning_goal_id = g.id AND en.lang = 'en'
             WHERE g.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function createGoal(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO learning_goals (progress, sort_order) VALUES (:progress, :sort_order)"
        );
        $stmt->execute([':progress' => (int)($data['progress']??0), ':sort_order' => (int)($data['sort_order']??0)]);
        $id = (int) $db->lastInsertId();
        foreach (['nl', 'en'] as $lang) {
            $resources = $this->parseResources($data["resources_{$lang}"] ?? '');
            $db->prepare(
                "INSERT INTO learning_goal_translations (learning_goal_id, lang, title, description, timeline, resources)
                 VALUES (:id, :lang, :title, :description, :timeline, :resources)"
            )->execute([
                ':id'          => $id,
                ':lang'        => $lang,
                ':title'       => $data["title_{$lang}"] ?? '',
                ':description' => $data["description_{$lang}"] ?? null,
                ':timeline'    => $data["timeline_{$lang}"] ?? null,
                ':resources'   => json_encode($resources),
            ]);
        }
        return $id;
    }

    public function updateGoal(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare("UPDATE learning_goals SET progress=:p, sort_order=:s, updated_at=NOW() WHERE id=:id")
           ->execute([':p' => (int)($data['progress']??0), ':s' => (int)($data['sort_order']??0), ':id' => $id]);
        foreach (['nl', 'en'] as $lang) {
            $resources = $this->parseResources($data["resources_{$lang}"] ?? '');
            $db->prepare(
                "INSERT INTO learning_goal_translations (learning_goal_id, lang, title, description, timeline, resources)
                 VALUES (:id, :lang, :title, :description, :timeline, :resources)
                 ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description),
                   timeline=VALUES(timeline), resources=VALUES(resources)"
            )->execute([
                ':id'          => $id,
                ':lang'        => $lang,
                ':title'       => $data["title_{$lang}"] ?? '',
                ':description' => $data["description_{$lang}"] ?? null,
                ':timeline'    => $data["timeline_{$lang}"] ?? null,
                ':resources'   => json_encode($resources),
            ]);
        }
    }

    public function deleteGoal(int $id): void {
        Database::getConnection()->prepare("DELETE FROM learning_goals WHERE id = :id")->execute([':id' => $id]);
    }

    public function countGoals(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM learning_goals")->fetchColumn();
    }

    public function countEducation(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM education_items")->fetchColumn();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function normalizeEducationRow(array $row): array {
        return [
            'id'              => $row['id'],
            'title'           => $row['title'],
            'institution'     => $row['institution'] ?? $row['title'],
            'period'          => $row['period'] ?? '',
            'description'     => $row['description'] ?? '',
            'skills'          => $row['skills_list'] ?? [],
            'certificate_url' => $row['certificate_url'] ?? '',
            'sort_order'      => $row['sort_order'],
        ];
    }

    private function normalizeLearningRow(array $row): array {
        return [
            'id'          => $row['id'],
            'title'       => $row['title'],
            'description' => $row['description'] ?? '',
            'progress'    => $row['progress'],
            'resources'   => $row['resources'] ?? [],
            'timeline'    => $row['timeline'] ?? '',
        ];
    }

    /**
     * Parse resources from textarea: "Name | https://url" per line
     */
    private function parseResources(string $raw): array {
        $lines     = array_filter(array_map('trim', explode("\n", $raw)));
        $resources = [];
        foreach ($lines as $line) {
            if (strpos($line, '|') !== false) {
                [$name, $url] = array_map('trim', explode('|', $line, 2));
                $resources[]  = ['name' => $name, 'url' => $url];
            } else {
                $resources[] = ['name' => $line, 'url' => ''];
            }
        }
        return $resources;
    }
}
