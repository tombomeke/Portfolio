<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_dev_skill_add') ?></span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=skill-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_name_required') ?></label>
                <input type="text" name="name" required autofocus placeholder="<?= trans('admin_dev_name_placeholder') ?>">
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_category_required') ?></label>
                <select name="category" required>
                    <option value="">— <?= trans('admin_faq_select') ?> —</option>
                    <option value="languages">Languages</option>
                    <option value="frameworks">Frameworks</option>
                    <option value="database">Database</option>
                    <option value="tools">Tools</option>
                </select>
            </div>
            <div class="form-group">
                <label><?= trans('modal_skill_level') ?></label>
                <select name="level">
                    <option value="1">1 – <?= trans('admin_dev_level_beginner') ?> / <?= trans('skills_level_beginner') ?></option>
                    <option value="2">2 – Intermediate / Basis</option>
                    <option value="3">3 – Advanced / Goed</option>
                </select>
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_order') ?></label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_dev_notes') ?></label>
                <input type="text" name="notes" placeholder="<?= trans('admin_dev_notes_placeholder') ?>">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_dev_used_in_projects') ?> <span style="color:var(--text-muted)">- <?= trans('admin_dev_one_per_line') ?></span></label>
                <textarea name="projects" rows="4" placeholder="Portfolio Website&#10;RPG Manager"></textarea>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
        </div>
    </form>
</div>
