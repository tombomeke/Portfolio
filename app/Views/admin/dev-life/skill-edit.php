<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<?php $projectsStr = implode("\n", $skill['projects'] ?? []); ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_dev_skill_edit') ?>: <?= htmlspecialchars($skill['name']) ?></span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=skill-edit&id=<?= $skill['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_name_required') ?></label>
                <input type="text" name="name" value="<?= htmlspecialchars($skill['name']) ?>" required>
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_category_required') ?></label>
                <select name="category" required>
                    <?php foreach (['languages','frameworks','database','tools'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $skill['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= trans('modal_skill_level') ?></label>
                <select name="level">
                    <?php foreach ([1=>'1 - '.trans('admin_dev_level_beginner'),2=>'2 - Intermediate',3=>'3 - Advanced'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $skill['level'] == $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_order') ?></label>
                <input type="number" name="sort_order" value="<?= $skill['sort_order'] ?>" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_dev_notes') ?></label>
                <input type="text" name="notes" value="<?= htmlspecialchars($skill['notes'] ?? '') ?>">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_dev_used_in_projects') ?> - <?= trans('admin_dev_one_per_line') ?></label>
                <textarea name="projects" rows="4"><?= htmlspecialchars($projectsStr) ?></textarea>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
        </div>
    </form>
</div>
