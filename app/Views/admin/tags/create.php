<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_tags_add') ?></span>
        <a href="?page=admin&section=tags" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=tags&action=create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem;padding:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_name_required') ?></label>
                <input type="text" name="name" required autofocus placeholder="<?= trans('admin_tags_name_placeholder') ?>">
            </div>
            <div class="form-group">
                <label>Slug <small style="font-weight:400"><?= trans('admin_tags_slug_optional_auto') ?></small></label>
                <input type="text" name="slug" placeholder="<?= trans('admin_tags_slug_placeholder') ?>">
                <span class="form-hint"><i class="fas fa-info-circle"></i> <?= trans('admin_tags_slug_hint') ?></span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_create') ?></button>
                <a href="?page=admin&section=tags" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
        </div>
    </form>
</div>
