<?php
// app/Models/FaqModel.php
require_once __DIR__ . '/../Config/Database.php';

class FaqModel {

    // ── Public ───────────────────────────────────────────────────────────────

    public function getAllWithItems($lang = 'nl') {
        $db = Database::getConnection();

        $cats = $db->prepare(
            "SELECT fc.id, fc.slug, fc.sort_order, fct.name
             FROM faq_categories fc
             JOIN faq_category_translations fct ON fct.faq_category_id = fc.id AND fct.lang = :lang
             ORDER BY fc.sort_order, fc.id"
        );
        $cats->execute([':lang' => $lang]);
        $categories = $cats->fetchAll();

        foreach ($categories as &$cat) {
            $items = $db->prepare(
                "SELECT fi.id, fi.sort_order, fit.question, fit.answer
                 FROM faq_items fi
                 JOIN faq_item_translations fit ON fit.faq_item_id = fi.id AND fit.lang = :lang
                 WHERE fi.faq_category_id = :cat_id
                 ORDER BY fi.sort_order, fi.id"
            );
            $items->execute([':lang' => $lang, ':cat_id' => $cat['id']]);
            $cat['items'] = $items->fetchAll();
        }

        return $categories;
    }

    // ── Admin: Categories ─────────────────────────────────────────────────────

    public function getAllCategoriesForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT fc.*,
                    nl.name AS name_nl,
                    en.name AS name_en,
                    (SELECT COUNT(*) FROM faq_items fi WHERE fi.faq_category_id = fc.id) AS item_count
             FROM faq_categories fc
             LEFT JOIN faq_category_translations nl ON nl.faq_category_id = fc.id AND nl.lang = 'nl'
             LEFT JOIN faq_category_translations en ON en.faq_category_id = fc.id AND en.lang = 'en'
             ORDER BY fc.sort_order, fc.id"
        );
        return $stmt->fetchAll();
    }

    public function getCategoryByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT fc.*,
                    nl.name AS name_nl,
                    en.name AS name_en
             FROM faq_categories fc
             LEFT JOIN faq_category_translations nl ON nl.faq_category_id = fc.id AND nl.lang = 'nl'
             LEFT JOIN faq_category_translations en ON en.faq_category_id = fc.id AND en.lang = 'en'
             WHERE fc.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function createCategory(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO faq_categories (slug, sort_order) VALUES (:slug, :sort_order)"
        );
        $stmt->execute([':slug' => $data['slug'], ':sort_order' => (int) ($data['sort_order'] ?? 0)]);
        $id = (int) $db->lastInsertId();

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO faq_category_translations (faq_category_id, lang, name)
                 VALUES (:cat_id, :lang, :name)"
            )->execute([':cat_id' => $id, ':lang' => $lang, ':name' => $data["name_{$lang}"]]);
        }
        return $id;
    }

    public function updateCategory(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE faq_categories SET slug=:slug, sort_order=:sort_order WHERE id=:id"
        )->execute([':slug' => $data['slug'], ':sort_order' => (int) ($data['sort_order'] ?? 0), ':id' => $id]);

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO faq_category_translations (faq_category_id, lang, name)
                 VALUES (:cat_id, :lang, :name)
                 ON DUPLICATE KEY UPDATE name=VALUES(name)"
            )->execute([':cat_id' => $id, ':lang' => $lang, ':name' => $data["name_{$lang}"]]);
        }
    }

    public function deleteCategory(int $id): void {
        Database::getConnection()->prepare("DELETE FROM faq_categories WHERE id = :id")->execute([':id' => $id]);
    }

    // ── Admin: Items ──────────────────────────────────────────────────────────

    public function getAllItemsForAdmin(): array {
        $db   = Database::getConnection();
        $stmt = $db->query(
            "SELECT fi.*,
                    fc.slug AS category_slug,
                    nl_c.name AS category_name,
                    nl.question AS question_nl, nl.answer AS answer_nl,
                    en.question AS question_en, en.answer AS answer_en
             FROM faq_items fi
             JOIN faq_categories fc ON fc.id = fi.faq_category_id
             LEFT JOIN faq_category_translations nl_c ON nl_c.faq_category_id = fc.id AND nl_c.lang = 'nl'
             LEFT JOIN faq_item_translations nl ON nl.faq_item_id = fi.id AND nl.lang = 'nl'
             LEFT JOIN faq_item_translations en ON en.faq_item_id = fi.id AND en.lang = 'en'
             ORDER BY fc.sort_order, fc.id, fi.sort_order, fi.id"
        );
        return $stmt->fetchAll();
    }

    public function getItemByIdForAdmin(int $id): ?array {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT fi.*,
                    nl.question AS question_nl, nl.answer AS answer_nl,
                    en.question AS question_en, en.answer AS answer_en
             FROM faq_items fi
             LEFT JOIN faq_item_translations nl ON nl.faq_item_id = fi.id AND nl.lang = 'nl'
             LEFT JOIN faq_item_translations en ON en.faq_item_id = fi.id AND en.lang = 'en'
             WHERE fi.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function createItem(array $data): int {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO faq_items (faq_category_id, sort_order) VALUES (:cat_id, :sort_order)"
        );
        $stmt->execute([':cat_id' => (int) $data['faq_category_id'], ':sort_order' => (int) ($data['sort_order'] ?? 0)]);
        $id = (int) $db->lastInsertId();

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO faq_item_translations (faq_item_id, lang, question, answer)
                 VALUES (:item_id, :lang, :question, :answer)"
            )->execute([
                ':item_id'  => $id,
                ':lang'     => $lang,
                ':question' => $data["question_{$lang}"],
                ':answer'   => $data["answer_{$lang}"],
            ]);
        }
        return $id;
    }

    public function updateItem(int $id, array $data): void {
        $db = Database::getConnection();
        $db->prepare(
            "UPDATE faq_items SET faq_category_id=:cat_id, sort_order=:sort_order WHERE id=:id"
        )->execute([':cat_id' => (int) $data['faq_category_id'], ':sort_order' => (int) ($data['sort_order'] ?? 0), ':id' => $id]);

        foreach (['nl', 'en'] as $lang) {
            $db->prepare(
                "INSERT INTO faq_item_translations (faq_item_id, lang, question, answer)
                 VALUES (:item_id, :lang, :question, :answer)
                 ON DUPLICATE KEY UPDATE question=VALUES(question), answer=VALUES(answer)"
            )->execute([
                ':item_id'  => $id,
                ':lang'     => $lang,
                ':question' => $data["question_{$lang}"],
                ':answer'   => $data["answer_{$lang}"],
            ]);
        }
    }

    public function deleteItem(int $id): void {
        Database::getConnection()->prepare("DELETE FROM faq_items WHERE id = :id")->execute([':id' => $id]);
    }

    public function countCategories(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM faq_categories")->fetchColumn();
    }

    public function countItems(): int {
        return (int) Database::getConnection()->query("SELECT COUNT(*) FROM faq_items")->fetchColumn();
    }
}
