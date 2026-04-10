<?php
require_once __DIR__ . '/../Config/Database.php';

class FaqModel {

    /** Alle categorieën met hun items voor de gegeven taal */
    public function getAllWithItems(string $lang = 'nl'): array {
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
}
