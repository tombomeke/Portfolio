<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-hard-hat"></i> WIP Pagina's</span>
    </div>
    <p style="color:var(--text-muted);font-size:.875rem;margin-bottom:1.5rem">
        <?= trans('admin_wip_intro_1') ?>
        <?= trans('admin_wip_intro_2') ?>
    </p>

    <form method="POST" action="?page=admin&section=wip">
        <?= \Auth::csrfField() ?>
        <div class="wip-page-list">
            <?php foreach ($knownPages as $slug => $label): ?>
            <label class="wip-toggle-row <?= in_array($slug, $current, true) ? 'wip-toggle-row--active' : '' ?>">
                <input type="checkbox" name="wip[<?= htmlspecialchars($slug) ?>]" value="1"
                       <?= in_array($slug, $current, true) ? 'checked' : '' ?>>
                <span class="wip-page-info">
                    <span class="wip-page-name"><?= htmlspecialchars($label) ?></span>
                    <span class="wip-page-slug">?page=<?= htmlspecialchars($slug) ?></span>
                </span>
                <span class="wip-status-badge <?= in_array($slug, $current, true) ? 'wip-status-badge--on' : 'wip-status-badge--off' ?>">
                    <?= in_array($slug, $current, true) ? trans('admin_wip_badge_wip') : trans('admin_wip_badge_live') ?>
                </span>
            </label>
            <?php endforeach; ?>
        </div>
        <div class="form-actions" style="margin-top:1.5rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
        </div>
    </form>
</div>

<style>
.wip-page-list { display: flex; flex-direction: column; gap: .5rem; }
.wip-toggle-row {
    display: flex; align-items: center; gap: 1rem;
    padding: .85rem 1rem; border-radius: 8px; cursor: pointer;
    border: 1px solid var(--border);
    background: var(--bg); transition: border-color .15s, background .15s;
}
.wip-toggle-row:hover { border-color: var(--primary); background: rgba(59,130,246,.04); }
.wip-toggle-row--active { border-color: rgba(245,158,11,.4); background: rgba(245,158,11,.05); }
.wip-toggle-row input[type="checkbox"] { width: 16px; height: 16px; flex-shrink: 0; accent-color: var(--warning); }
.wip-page-info { flex: 1; display: flex; flex-direction: column; gap: .15rem; }
.wip-page-name { font-size: .9rem; font-weight: 500; }
.wip-page-slug { font-size: .75rem; color: var(--text-muted); font-family: monospace; }
.wip-status-badge { padding: .2rem .6rem; border-radius: 99px; font-size: .7rem; font-weight: 600; }
.wip-status-badge--on  { background: rgba(245,158,11,.15); color: #fcd34d; }
.wip-status-badge--off { background: rgba(34,197,94,.12);  color: #86efac; }
</style>
