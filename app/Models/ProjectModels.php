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
        return $row ? $this->decodeRow($row, true) : null;
    }

    public function getProjectBySlug(string $slug): ?array {
        $lang = Translations::getCurrentLang();
        $db   = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT p.id, p.slug, p.category, p.status, p.image_path, p.repo_url, p.demo_url, p.tech,
                    t.title, t.description, t.long_description, t.features
             FROM   projects p
             JOIN   project_translations t ON t.project_id = p.id AND t.lang = :lang
             WHERE  p.slug = :slug LIMIT 1"
        );
        $stmt->execute([':lang' => $lang, ':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ? $this->decodeRow($row, true) : null;
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

    // ── Gallery ──────────────────────────────────────────────────────────────

    /**
     * Batch-load gallery images for multiple projects in a single query.
     * Returns [projectId => [image_path, ...]] for efficient use on listing pages.
     */
    public function getGalleryImagesForProjects(array $projectIds): array {
        if (empty($projectIds)) return [];

        $db          = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        $stmt        = $db->prepare(
            "SELECT project_id, image_path FROM project_images
             WHERE project_id IN ({$placeholders})
             ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute(array_values($projectIds));

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $pid = (int) $row['project_id'];
            $result[$pid][] = (string) $row['image_path'];
        }
        return $result;
    }

    public function getImagesByProjectId(int $projectId): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT id, image_path, caption, sort_order
             FROM   project_images
             WHERE  project_id = :project_id
             ORDER  BY sort_order ASC, id ASC"
        );
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll();
    }

    // TODO(gallery): add drag-and-drop sort_order reordering in admin edit view
    public function addImage(int $projectId, string $imagePath, int $sortOrder = 0): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO project_images (project_id, image_path, sort_order)
             VALUES (:project_id, :image_path, :sort_order)"
        );
        $stmt->execute([
            ':project_id' => $projectId,
            ':image_path' => $imagePath,
            ':sort_order' => $sortOrder,
        ]);
        return (int) $db->lastInsertId();
    }

    public function deleteImage(int $imageId): void {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT image_path FROM project_images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        $row = $stmt->fetch();

        if ($row && $row['image_path']) {
            $fullPath = __DIR__ . '/../../' . $row['image_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $db->prepare("DELETE FROM project_images WHERE id = :id")->execute([':id' => $imageId]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function decodeRow(array $row, bool $withGallery = false): array {
        $row['tech']     = json_decode($row['tech']     ?? '[]', true) ?? [];
        $row['features'] = json_decode($row['features'] ?? '[]', true) ?? [];
        $imagePath = (string) ($row['image_path'] ?? '');
        $row['image'] = $imagePath;

        $images = $imagePath !== '' ? [$imagePath] : [];

        // Merge gallery images for detail pages (avoids N+1 on list pages)
        if ($withGallery && isset($row['id'])) {
            $galleryRows = $this->getImagesByProjectId((int) $row['id']);
            foreach ($galleryRows as $galleryRow) {
                $path = (string) $galleryRow['image_path'];
                if ($path !== '' && !in_array($path, $images, true)) {
                    $images[] = $path;
                }
            }
        }

        $row['images'] = $images;
        return $row;
    }
}
