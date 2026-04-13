<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_faq_add_category') ?></span>
        <a href="?page=admin&section=faq" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=faq&action=category-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label>Slug *</label>
                <input type="text" name="slug" required pattern="[a-z0-9\-]+" placeholder="<?= trans('admin_faq_slug_placeholder') ?>">
                <span class="form-hint"><?= trans('admin_faq_slug_hint') ?></span>
            </div>
            <div class="form-group">
                <label><?= trans('admin_faq_order') ?></label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group">
                <label><?= trans('admin_faq_name_nl_required') ?></label>
                <input type="text" name="name_nl" required>
            </div>
            <div class="form-group">
                <label>Name (EN) *</label>
                <input type="text" name="name_en" required>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
            <a href="?page=admin&section=faq" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
        </div>
    </form>
</div>
