<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="card" style="margin-bottom:1rem">
    <form method="GET" style="padding:.85rem 1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="section" value="activity-logs">
        <div class="form-group" style="margin:0;flex:1;min-width:180px">
            <label><?= trans('admin_search_label') ?></label>
            <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="<?= trans('admin_activity_search_placeholder') ?>">
        </div>
        <div class="form-group" style="margin:0;min-width:150px">
            <label><?= trans('admin_table_action') ?></label>
            <select name="action_filter">
                <option value=""><?= trans('admin_activity_all_actions') ?></option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($a)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;padding-top:1.2rem">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> <?= trans('admin_filter') ?></button>
            <?php if (!empty($filters['search']) || !empty($filters['action'])): ?>
                <a href="?page=admin&section=activity-logs" class="btn btn-ghost btn-sm"><i class="fas fa-times"></i> <?= trans('admin_clear') ?></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-history"></i> <?= trans('admin_activity_log') ?> <small style="font-weight:400;color:var(--text-muted)">(<?= $total ?> <?= trans('admin_entries') ?>)</small></span>
        <form method="POST" action="?page=admin&section=activity-logs&action=clear" style="display:flex;gap:.4rem;align-items:center">
            <?= \Auth::csrfField() ?>
            <input type="number" name="older_than" value="30" min="1" max="365" style="width:70px" title="<?= trans('admin_activity_older_than_days') ?>">
            <button type="submit" class="btn btn-ghost btn-sm" data-confirm="<?= trans('admin_activity_clear_confirm') ?>">
                <i class="fas fa-trash-alt"></i> <?= trans('admin_cleanup') ?>
            </button>
        </form>
    </div>

    <?php if (empty($logs)): ?>
        <p class="empty-state"><i class="fas fa-history"></i> <?= trans('admin_activity_no_logs') ?></p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th style="width:140px"><?= trans('admin_table_timestamp') ?></th>
                <th style="width:110px"><?= trans('admin_table_action') ?></th>
                <th><?= trans('admin_table_description') ?></th>
                <th style="width:120px"><?= trans('admin_table_user') ?></th>
                <th style="width:110px">IP</th>
                <th style="width:50px"></th>
            </tr>
        </thead>
        <tbody>
        <?php
        $logModel = new ActivityLogModel();
        foreach ($logs as $log):
            $color = $logModel->getActionColor($log['action']);
            $icon  = $logModel->getActionIcon($log['action']);
            $label = $logModel->getActionLabel($log['action']);
        ?>
        <tr>
            <td class="text-muted text-sm" title="<?= date('d M Y H:i:s', strtotime($log['created_at'])) ?>">
                <?= date('d/m H:i', strtotime($log['created_at'])) ?>
            </td>
            <td>
                <span class="badge <?= $color ? 'badge--'.$color : '' ?>">
                    <i class="<?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($label) ?>
                </span>
            </td>
            <td>
                <?= htmlspecialchars($log['description']) ?>
                <?php if ($log['model_type']): ?>
                    <small class="text-muted"><?= htmlspecialchars(basename($log['model_type'])) ?> #<?= (int)$log['model_id'] ?></small>
                <?php endif; ?>
            </td>
            <td class="text-sm"><?= htmlspecialchars($log['username'] ?? trans('admin_system_user')) ?></td>
            <td><code style="font-size:.75rem"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></td>
            <td>
                <form method="POST" action="?page=admin&section=activity-logs&action=delete&id=<?= $log['id'] ?>">
                    <?= \Auth::csrfField() ?>
                    <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger)" data-confirm="<?= trans('admin_activity_delete_confirm') ?>"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($total > $perPage): ?>
    <div style="padding:1rem 1.25rem;display:flex;gap:.5rem;justify-content:center">
        <?php
        $totalPages = (int) ceil($total / $perPage);
        $qs = http_build_query(array_filter(['page'=>'admin','section'=>'activity-logs','action_filter'=>$filters['action'],'search'=>$filters['search']]));
        for ($p = 1; $p <= $totalPages; $p++):
        ?>
            <a href="?<?= $qs ?>&p=<?= $p ?>" class="btn btn-ghost btn-sm <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
