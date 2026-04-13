<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php
// Format resources back to "Name | url" per line
function formatResources(?string $json): string {
    $items = json_decode($json ?? '[]', true) ?? [];
    return implode("\n", array_map(fn($r) => is_array($r) ? trim($r['name'].' | '.($r['url']??''), ' |') : $r, $items));
}
$resNl = formatResources($goal['resources_nl'] ?? null);
$resEn = formatResources($goal['resources_en'] ?? null);
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_dev_goal_edit') ?></span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=goal-edit&id=<?= $goal['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;margin-bottom:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_dev_progress_0_100') ?></label>
                <input type="number" name="progress" value="<?= $goal['progress'] ?>" min="0" max="100">
            </div>
            <div class="form-group">
                <label><?= trans('admin_dev_order') ?></label>
                <input type="number" name="sort_order" value="<?= $goal['sort_order'] ?>" min="0">
            </div>
        </div>
        <div>
            <div class="lang-tabs lang-tab-group">
                <button type="button" class="lang-tab active" data-target="goal-nl">🇳🇱 Nederlands</button>
                <button type="button" class="lang-tab" data-target="goal-en">🇬🇧 English</button>
            </div>
            <?php foreach (['nl' => $resNl, 'en' => $resEn] as $lang => $resStr): ?>
            <div id="goal-<?= $lang ?>" class="lang-panel <?= $lang === 'nl' ? 'active' : '' ?>">
                <div class="form-grid" style="gap:1rem">
                    <div class="form-group"><label><?= trans('admin_dev_title') ?> *</label><input type="text" name="title_<?= $lang ?>" value="<?= htmlspecialchars($goal["title_{$lang}"] ?? '') ?>" <?= $lang === 'nl' ? 'required' : '' ?>></div>
                    <div class="form-group"><label><?= trans('modal_education_description') ?></label><textarea name="description_<?= $lang ?>"><?= htmlspecialchars($goal["description_{$lang}"] ?? '') ?></textarea></div>
                    <div class="form-group"><label><?= trans('modal_learning_timeline') ?></label><input type="text" name="timeline_<?= $lang ?>" value="<?= htmlspecialchars($goal["timeline_{$lang}"] ?? '') ?>"></div>
                    <div class="form-group">
                        <label><?= trans('modal_learning_resources') ?> - <?= trans('admin_dev_one_per_line') ?>: <code><?= trans('admin_dev_resource_format') ?></code></label>
                        <textarea name="resources_<?= $lang ?>" rows="4"><?= htmlspecialchars($resStr) ?></textarea>
                    </div>
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
