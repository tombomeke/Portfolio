<?php
// app/Models/NewsModel.php
require_once __DIR__ . '/../Config/Database.php';

class NewsModel {

    // ── Public ───────────────────────────────────────────────────────────────

    public function getAll($lang = 'nl', $limit = 20, $offset = 0) {
        $db  = Database::getConnection();
        $sql = "SELECT n.id, n.image_path, n.published_at,
                       t.title, t.content
                FROM   news_items n
                JOIN   news_item_translations t
                       ON t.news_item_id = n.id AND t.lang = :lang
                WHERE  n.published_at IS NOT NULL AND n.published_at <= NOW()
                ORDER  BY n.published_at DESC
                LIMIT  :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':lang',   $lang,   PDO::PARAM_STR);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id, $lang = 'nl') {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT n.id, n.image_path, n.published_at,
                    t.title, t.content
             FROM   news_items n
             JOIN   news_item_translations t
                    ON t.news_item_id = n.id AND t.lang = :lang
             WHERE  n.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':lang' => $lang]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function count($lang = 'nl') {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM news_items n
             JOIN news_item_translations t ON t.news_item_id = n.id AND t.lang = :lang
             WHERE n.published_at IS NOT NULL AND n.published_at <= NOW()"
        );
        $stmt->execute([':lang' => $lang]);
        return (int) $stmt->fetchColumn();
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    public function getAllForAdmin(int $limit = 50, int $offset = 0): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT n.*,
                    nl.title AS title_nl, nl.content AS content_nl,
                    en.title AS title_en, en.content AS content_en
             FROM   news_items n
             LEFT JOIN news_item_translations nl ON nl.news_item_id = n.id AND nl.lang = 'nl'
             LEFT JOIN news_item_translations en ON en.news_item_id = n.id AND en.lang = 'en'
             ORDER  BY n.created_at DESC
             LIMIT  :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT n.*,
                    nl.title AS title_nl, nl.content AS content_nl,
                    en.title AS title_en, en.content AS content_en
             FROM   news_items n
             LEFT JOIN news_item_translations nl ON nl.news_item_id = n.id AND nl.lang = 'nl'
             LEFT JOIN news_item_translations en ON en.news_item_id = n.id AND en.lang = 'en'
             WHERE  n.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function countAll(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM news_items")->fetchColumn();
    }

    public function create(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO news_items (image_path, published_at) VALUES (:image_path, :published_at)"
        );
        $stmt->execute([
            ':image_path'  => $data['image_path']  ?? null,
            ':published_at' => $data['published_at'] ?: null,
        ]);
        $id = (int) $db->lastInsertId();

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO news_item_translations (news_item_id, lang, title, content)
                 VALUES (:news_item_id, :lang, :title, :content)"
            )->execute([
                ':news_item_id' => $id,
                ':lang'         => $lang,
                ':title'        => $data["title_{$lang}"],
                ':content'      => $data["content_{$lang}"],
            ]);
        }
        return $id;
    }

    public function update(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE news_items SET image_path=:image_path, published_at=:published_at, updated_at=NOW()
             WHERE id=:id"
        )->execute([
            ':image_path'  => $data['image_path']  ?? null,
            ':published_at' => $data['published_at'] ?: null,
            ':id'           => $id,
        ]);

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO news_item_translations (news_item_id, lang, title, content)
                 VALUES (:news_item_id, :lang, :title, :content)
                 ON DUPLICATE KEY UPDATE title=VALUES(title), content=VALUES(content), updated_at=NOW()"
            )->execute([
                ':news_item_id' => $id,
                ':lang'         => $lang,
                ':title'        => $data["title_{$lang}"],
                ':content'      => $data["content_{$lang}"],
            ]);
        }
    }

    public function delete(int $id): void {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT image_path FROM news_items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row && $row['image_path']) {
            $fullPath = __DIR__ . '/../../' . $row['image_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $db->prepare("DELETE FROM news_items WHERE id = :id")->execute([':id' => $id]);
    }
}
