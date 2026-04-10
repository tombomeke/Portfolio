<?php
require_once __DIR__ . '/../Config/Database.php';

class NewsModel {

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
}
