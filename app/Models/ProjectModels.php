<?php
// app/Models/ProjectModels.php — DB-driven (migrated from static array)
require_once __DIR__ . '/../Config/Database.php';

class ProjectModel {

    // ── Public-facing ────────────────────────────────────────────────────────

    public function getAllProjects(): array {
        $lang = Translations::getCurrentLang();
        $db   = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT p.id, p.slug, p.category, p.status, p.image_path, p.repo_url, p.demo_url, p.tech,
                    t.title, t.description, t.long_description, t.features
             FROM   projects p
             JOIN   project_translations t ON t.project_id = p.id AND t.lang = :lang
             ORDER  BY p.sort_order ASC, p.id ASC"
        );
        $stmt->execute([':lang' => $lang]);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'decodeRow'], $rows);
    }

    public function getProjectById(int $id): ?array {
        $lang = Translations::getCurrentLang();
        $db   = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT p.id, p.slug, p.category, p.status, p.image_path, p.repo_url, p.demo_url, p.tech,
                    t.title, t.description, t.long_description, t.features
             FROM   projects p
             JOIN   project_translations t ON t.project_id = p.id AND t.lang = :lang
             WHERE  p.id = :id LIMIT 1"
        );
        $stmt->execute([':lang' => $lang, ':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->decodeRow($row) : null;
    }

    public function getProjectsByCategory(string $category): array {
        return array_values(
            array_filter($this->getAllProjects(), fn($p) => $p['category'] === $category)
        );
    }

    public function getModalData(array $project): string {
        return htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8');
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function getAllForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT p.*,
                    nl.title           AS title_nl,  nl.description AS desc_nl,
                    nl.long_description AS long_desc_nl, nl.features AS features_nl,
                    en.title           AS title_en,  en.description AS desc_en,
                    en.long_description AS long_desc_en, en.features AS features_en
             FROM   projects p
             LEFT JOIN project_translations nl ON nl.project_id = p.id AND nl.lang = 'nl'
             LEFT JOIN project_translations en ON en.project_id = p.id AND en.lang = 'en'
             ORDER  BY p.sort_order ASC, p.id ASC"
        );
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['tech']        = json_decode($row['tech']        ?? '[]', true) ?? [];
            $row['features_nl'] = json_decode($row['features_nl'] ?? '[]', true) ?? [];
            $row['features_en'] = json_decode($row['features_en'] ?? '[]', true) ?? [];
        }
        return $rows;
    }

    public function getByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT p.*,
                    nl.title            AS title_nl, nl.description AS desc_nl,
                    nl.long_description AS long_desc_nl, nl.features AS features_nl,
                    en.title            AS title_en, en.description AS desc_en,
                    en.long_description AS long_desc_en, en.features AS features_en
             FROM   projects p
             LEFT JOIN project_translations nl ON nl.project_id = p.id AND nl.lang = 'nl'
             LEFT JOIN project_translations en ON en.project_id = p.id AND en.lang = 'en'
             WHERE  p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $row['tech']        = json_decode($row['tech']        ?? '[]', true) ?? [];
        $row['features_nl'] = json_decode($row['features_nl'] ?? '[]', true) ?? [];
        $row['features_en'] = json_decode($row['features_en'] ?? '[]', true) ?? [];
        return $row;
    }

    public function create(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO projects (slug, category, status, image_path, repo_url, demo_url, tech, sort_order)
             VALUES (:slug, :category, :status, :image_path, :repo_url, :demo_url, :tech, :sort_order)"
        );
        $stmt->execute([
            ':slug'       => $data['slug'],
            ':category'   => $data['category'],
            ':status'     => $data['status']     ?? null,
            ':image_path' => $data['image_path'] ?? null,
            ':repo_url'   => $data['repo_url']   ?? null,
            ':demo_url'   => $data['demo_url']   ?? null,
            ':tech'       => json_encode($data['tech'] ?? []),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        $id = (int) $db->lastInsertId();

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO project_translations (project_id, lang, title, description, long_description, features)
                 VALUES (:project_id, :lang, :title, :description, :long_description, :features)"
            )->execute([
                ':project_id'       => $id,
                ':lang'             => $lang,
                ':title'            => $data["title_{$lang}"],
                ':description'      => $data["description_{$lang}"],
                ':long_description' => $data["long_description_{$lang}"] ?? null,
                ':features'         => json_encode($data["features_{$lang}"] ?? []),
            ]);
        }
        return $id;
    }

    public function update(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE projects
             SET slug=:slug, category=:category, status=:status, image_path=:image_path,
                 repo_url=:repo_url, demo_url=:demo_url, tech=:tech, sort_order=:sort_order,
                 updated_at=NOW()
             WHERE id=:id"
        )->execute([
            ':slug'       => $data['slug'],
            ':category'   => $data['category'],
            ':status'     => $data['status']     ?? null,
            ':image_path' => $data['image_path'] ?? null,
            ':repo_url'   => $data['repo_url']   ?? null,
            ':demo_url'   => $data['demo_url']   ?? null,
            ':tech'       => json_encode($data['tech'] ?? []),
            ':sort_order' => (int) ($data['sort_order'] ?? 0),
            ':id'         => $id,
        ]);

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO project_translations (project_id, lang, title, description, long_description, features)
                 VALUES (:project_id, :lang, :title, :description, :long_description, :features)
                 ON DUPLICATE KEY UPDATE
                   title=VALUES(title), description=VALUES(description),
                   long_description=VALUES(long_description), features=VALUES(features), updated_at=NOW()"
            )->execute([
                ':project_id'       => $id,
                ':lang'             => $lang,
                ':title'            => $data["title_{$lang}"],
                ':description'      => $data["description_{$lang}"],
                ':long_description' => $data["long_description_{$lang}"] ?? null,
                ':features'         => json_encode($data["features_{$lang}"] ?? []),
            ]);
        }
    }

    public function delete(int $id): void {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT image_path FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row && $row['image_path']) {
            $fullPath = __DIR__ . '/../../' . $row['image_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $db->prepare("DELETE FROM projects WHERE id = :id")->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function decodeRow(array $row): array {
        $row['tech']     = json_decode($row['tech']     ?? '[]', true) ?? [];
        $row['features'] = json_decode($row['features'] ?? '[]', true) ?? [];
        return $row;
    }
}
