<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="?page=admin&section=faq&action=category-create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> <?= trans('admin_faq_add_category') ?>
    </a>
    <a href="?page=admin&section=faq&action=item-create" class="btn btn-ghost btn-sm">
        <i class="fas fa-plus"></i> <?= trans('admin_faq_add_item') ?>
    </a>
</div>

<?php if (empty($categories)): ?>
    <div class="card"><p style="color:var(--text-muted)"><?= trans('admin_faq_no_categories') ?></p></div>
<?php else: ?>
    <?php foreach ($categories as $cat): ?>
    <div class="card" style="margin-bottom:1rem">
        <div class="card-header">
            <div>
                <span class="card-title"><?= htmlspecialchars($cat['name_nl'] ?? $cat['slug']) ?></span>
                <span style="margin-left:.5rem;font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars($cat['name_en'] ?? '') ?> · slug: <?= htmlspecialchars($cat['slug']) ?> · <?= trans('admin_faq_order') ?>: <?= $cat['sort_order'] ?> · <?= $cat['item_count'] ?> <?= trans('roadmap_items_label') ?></span>
            </div>
            <div style="display:flex;gap:.4rem">
                <a href="?page=admin&section=faq&action=item-create&cat=<?= $cat['id'] ?>" class="btn btn-ghost btn-sm">
                    <i class="fas fa-plus"></i> <?= trans('admin_faq_item') ?>
                </a>
                <a href="?page=admin&section=faq&action=category-edit&id=<?= $cat['id'] ?>" class="btn btn-ghost btn-sm">
                    <i class="fas fa-pencil"></i>
                </a>
                <form method="POST" action="?page=admin&section=faq&action=category-delete&id=<?= $cat['id'] ?>" class="confirm-inline">
                    <?= \Auth::csrfField() ?>
                    <button type="submit" data-confirm="<?= trans('admin_faq_delete_category_confirm') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
