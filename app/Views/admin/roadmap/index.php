<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php $defaultRepoUrl = !empty($config['repoUrl']) ? (string) $config['repoUrl'] : 'https://github.com/tombomeke/Portfolio'; ?>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-code"></i> <?= trans('admin_roadmap_title') ?></span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:1rem">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="sync">

        <div class="form-group">
            <label><?= trans('admin_roadmap_repo_url') ?></label>
            <input
                type="url"
                name="repo_url"
                class="form-input"
                placeholder="https://github.com/owner/repo"
                value="<?= htmlspecialchars($defaultRepoUrl) ?>"
                required
            >
            <span class="form-hint"><?= trans('admin_roadmap_repo_hint') ?></span>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="todos_only" value="1" checked>
                <span><?= trans('admin_roadmap_todos_only') ?></span>
            </label>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="merge_mode" value="1" checked>
                <span><?= trans('admin_roadmap_merge_mode') ?></span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-rotate"></i> <?= trans('admin_roadmap_sync_btn') ?></button>
        </div>
    </form>

    <form method="POST" action="?page=admin&section=roadmap" style="margin-top:.75rem" onsubmit="return confirm('<?= addslashes(trans('admin_roadmap_cleanup_confirm')) ?>')">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="cleanup">
        <button type="submit" class="btn btn-secondary"><i class="fas fa-filter-circle-xmark"></i> <?= trans('admin_roadmap_cleanup_btn') ?></button>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-list-check"></i> <?= trans('admin_roadmap_items_title') ?></span>
        <span class="badge"><?= count((array) ($config['items'] ?? [])) ?> <?= trans('admin_roadmap_items_count') ?></span>
    </div>

    <?php if (!empty($config['lastSyncAt'])): ?>
        <p class="text-muted text-sm" style="margin:0 0 1rem">
            <?= trans('admin_roadmap_last_sync') ?>: <?= date('d/m/Y H:i', strtotime((string) $config['lastSyncAt'])) ?>
            · <?= trans('admin_roadmap_source') ?>: <?= htmlspecialchars((string) ($config['source'] ?? 'manual')) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($config['items'])): ?>
        <p class="empty-state"><i class="fas fa-inbox"></i> <?= trans('admin_roadmap_no_items') ?></p>
    <?php else: ?>
        <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:.75rem">
            <?= \Auth::csrfField() ?>
            <input type="hidden" name="roadmap_action" value="save">

            <?php foreach ($config['items'] as $item): ?>
                <?php $id = (string) ($item['id'] ?? ''); ?>
                <?php $isDone = (($item['status'] ?? 'todo') === 'done'); ?>
                <label style="display:flex;align-items:flex-start;gap:.75rem;padding:.7rem .85rem;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:<?= $isDone ? 'rgba(34,197,94,.06)' : 'rgba(245,158,11,.05)' ?>">
                    <input type="checkbox" name="done[]" value="<?= htmlspecialchars($id) ?>" <?= $isDone ? 'checked' : '' ?>>
                    <span>
                        <strong style="display:block"><?= htmlspecialchars((string) ($item['title'] ?? 'Roadmap item')) ?></strong>
                        <?php if (!empty($item['priority']) && $item['priority'] !== 'normal'): ?>
                            <small class="text-muted" style="display:inline-block;margin-top:.2rem">Prioriteit: <?= htmlspecialchars((string) $item['priority']) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item['sourceLine']) || !empty($item['sourceSection'])): ?>
                            <small class="text-muted" style="display:block">
                                <?php if (!empty($item['sourceSection'])): ?>Bestand: <?= htmlspecialchars((string) $item['sourceSection']) ?><?php endif; ?>
                                <?php if (!empty($item['sourceLine'])): ?><?= !empty($item['sourceSection']) ? ' · ' : '' ?>Regel: #<?= (int) $item['sourceLine'] ?><?php endif; ?>
                            </small>
                        <?php endif; ?>
                        <?php if (!empty($item['description'])): ?>
                            <small class="text-muted"><?= htmlspecialchars((string) $item['description']) ?></small>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_roadmap_save_status') ?></button>
            </div>
        </form>
    <?php endif; ?>
</div>

    <div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-arrow-rotate-left"></i> <?= trans('admin_roadmap_reset_title') ?></span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" onsubmit="return confirm('<?= addslashes(trans('admin_roadmap_reset_confirm')) ?>')">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="reset">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> <?= trans('admin_roadmap_reset_btn') ?></button>
    </form>
</div>

<div class="card" style="margin-top:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-circle-info"></i> <?= trans('admin_roadmap_source_title') ?></span>
    </div>
    <p class="text-muted text-sm" style="margin:0">
        <?= trans('admin_roadmap_source_info') ?>
    </p>
</div>
