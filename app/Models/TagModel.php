<?php
// app/Models/TagModel.php
require_once __DIR__ . '/../Config/Database.php';

class TagModel {

    public function getAll(): array {
        $db = Database::getConnection();
        return $db->query(
            "SELECT t.*, COUNT(nit.news_item_id) AS news_count
             FROM tags t
             LEFT JOIN news_item_tag nit ON nit.tag_id = t.id
             GROUP BY t.id
             ORDER BY t.name ASC"
        )->fetchAll();
    }

    public function getById(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tags WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getBySlug(string $slug): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM tags WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $slug): int {
        $db = Database::getConnection();
        $db->prepare("INSERT INTO tags (name, slug) VALUES (:name, :slug)")
           ->execute([':name' => $name, ':slug' => $slug]);
        return (int) $db->lastInsertId();
    }

    public function update(int $id, string $name, string $slug): void {
        Database::getConnection()->prepare(
            "UPDATE tags SET name=:name, slug=:slug, updated_at=NOW() WHERE id=:id"
        )->execute([':name' => $name, ':slug' => $slug, ':id' => $id]);
    }

    public function delete(int $id): void {
        Database::getConnection()->prepare("DELETE FROM tags WHERE id=:id")->execute([':id' => $id]);
    }

    public function count(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM tags")->fetchColumn();
    }

    public function nameExists(string $name, ?int $excludeId = null): bool {
        $db   = Database::getConnection();
        $sql  = "SELECT 1 FROM tags WHERE name=:name" . ($excludeId ? " AND id != :id" : "") . " LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':name', $name);
        if ($excludeId) $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool {
        $db   = Database::getConnection();
        $sql  = "SELECT 1 FROM tags WHERE slug=:slug" . ($excludeId ? " AND id != :id" : "") . " LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($excludeId) $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    // Get tags for a specific news item
    public function getForNewsItem(int $newsItemId): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT t.* FROM tags t
             JOIN news_item_tag nit ON nit.tag_id = t.id
             WHERE nit.news_item_id = :id ORDER BY t.name ASC"
        );
        $stmt->execute([':id' => $newsItemId]);
        return $stmt->fetchAll();
    }

    // Replace all tags for a news item
    public function syncForNewsItem(int $newsItemId, array $tagIds): void {
        $db = Database::getConnection();
        $db->prepare("DELETE FROM news_item_tag WHERE news_item_id = :id")->execute([':id' => $newsItemId]);
        if (empty($tagIds)) return;
        $stmt = $db->prepare("INSERT IGNORE INTO news_item_tag (news_item_id, tag_id) VALUES (:news_id, :tag_id)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([':news_id' => $newsItemId, ':tag_id' => (int) $tagId]);
        }
    }

    public static function slugify(string $name): string {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
