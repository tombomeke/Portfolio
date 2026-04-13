<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_dev_education_add') ?></span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=edu-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;margin-bottom:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_order') ?></label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_certificate_url_optional') ?></label>
                <input type="url" name="certificate_url" placeholder="https://...">
            </div>
        </div>
        <div>
            <div class="lang-tabs lang-tab-group">
                <button type="button" class="lang-tab active" data-target="edu-nl">🇳🇱 Nederlands</button>
                <button type="button" class="lang-tab" data-target="edu-en">🇬🇧 English</button>
            </div>
            <?php foreach ([['nl','NL',trans('modal_education_institution'),trans('modal_education_period'),trans('modal_education_description')],['en','EN','Institution','Period','Description']] as [$lang,$label,$inst,$per,$desc]): ?>
            <div id="edu-<?= $lang ?>" class="lang-panel <?= $lang === 'nl' ? 'active' : '' ?>">
                <div class="form-grid" style="gap:1rem">
                    <div class="form-group"><label><?= trans('admin_dev_title') ?> (<?= $label ?>) *</label><input type="text" name="title_<?= $lang ?>" <?= $lang === 'nl' ? 'required' : '' ?>></div>
                    <div class="form-group form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group"><label><?= $inst ?></label><input type="text" name="institution_<?= $lang ?>" placeholder="<?= trans('admin_dev_institution_placeholder') ?>"></div>
                        <div class="form-group"><label><?= $per ?></label><input type="text" name="period_<?= $lang ?>" placeholder="<?= trans('admin_dev_period_placeholder') ?>"></div>
                    </div>
                    <div class="form-group"><label><?= $desc ?></label><textarea name="description_<?= $lang ?>"></textarea></div>
                    <div class="form-group"><label><?= trans('admin_dev_skills_subjects') ?> - <?= trans('admin_dev_one_per_line') ?></label><textarea name="skills_<?= $lang ?>" rows="4" placeholder="Software Architectuur&#10;Team Collaboration"></textarea></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
        </div>
    </form>
</div>
