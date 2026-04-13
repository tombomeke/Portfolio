<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_tags_edit') ?>: <strong><?= htmlspecialchars($tag['name']) ?></strong></span>
        <a href="?page=admin&section=tags" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=tags&action=edit&id=<?= $tag['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem;padding:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_name_required') ?></label>
                <input type="text" name="name" value="<?= htmlspecialchars($tag['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Slug <small style="font-weight:400"><?= trans('admin_tags_optional') ?></small></label>
                <input type="text" name="slug" value="<?= htmlspecialchars($tag['slug']) ?>">
                <span class="form-hint"><i class="fas fa-info-circle"></i> <?= trans('admin_tags_edit_slug_hint') ?></span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
                <a href="?page=admin&section=tags" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
        </div>
    </form>
</div>
