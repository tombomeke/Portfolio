<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_projects_all') ?> (<?= count($projects) ?>)</span>
        <div style="display:flex;gap:.4rem;align-items:center">
            <form method="POST" action="?page=admin&section=projects&action=sync-all"
                  onsubmit="return confirm('<?= trans('admin_projects_sync_all_confirm') ?>')">
                <?= \Auth::csrfField() ?>
                <button type="submit" class="btn btn-ghost btn-sm" title="<?= trans('admin_projects_sync_all_title') ?>">
                    <i class="fas fa-rotate"></i> <?= trans('admin_projects_sync_roadmaps') ?>
                </button>
            </form>
            <a href="?page=admin&section=projects&action=create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> <?= trans('admin_add') ?>
            </a>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <p style="color:var(--text-muted)"><?= trans('admin_projects_none') ?> <a href="?page=admin&section=projects&action=create" style="color:var(--primary)"><?= trans('admin_dev_add_one') ?></a></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= trans('admin_dev_title_nl') ?></th>
                    <th><?= trans('modal_skill_category') ?></th>
                    <th><?= trans('admin_table_status') ?></th>
                    <th><?= trans('admin_dev_order') ?></th>
                    <th><?= trans('admin_actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title_nl'] ?? '—') ?></td>
                    <td><code style="font-size:.8rem"><?= htmlspecialchars($p['category']) ?></code></td>
                    <td>
                        <?php if ($p['status']): ?>
                            <span class="badge-status <?= htmlspecialchars($p['status']) ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= $p['sort_order'] ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=project&slug=<?= urlencode((string) ($p['slug'] ?? '')) ?>&tab=roadmap" class="btn btn-ghost btn-sm" title="<?= trans('admin_projects_open_public_roadmap') ?>">
                            <i class="fas fa-list-check"></i>
                        </a>
                        <a href="?page=admin&section=projects&action=edit&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=projects&action=delete&id=<?= $p['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="<?= trans('admin_projects_delete_confirm') ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php $syncLogs = $syncLogs ?? []; ?>
<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-rotate"></i> <?= trans('admin_projects_sync_logs') ?></span>
        <span class="badge"><?= count($syncLogs) ?> <?= trans('admin_projects_recent_entries') ?></span>
    </div>
    <?php if (empty($syncLogs)): ?>
        <p style="color:var(--text-muted);padding:.75rem 0">
            <?= trans('admin_projects_no_sync_logs') ?>
        </p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><?= trans('admin_table_timestamp') ?></th>
                    <th><?= trans('admin_projects_project') ?></th>
                    <th><?= trans('roadmap_items_label') ?></th>
                    <th><?= trans('admin_table_status') ?></th>
                    <th><?= trans('admin_projects_contract') ?></th>
                    <th><?= trans('admin_projects_error_message') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($syncLogs as $log): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:.8rem">
                        <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime((string) ($log['created_at'] ?? '')))) ?>
                    </td>
                    <td>
                        <?php if (!empty($log['project_slug'])): ?>
                            <a href="?page=project&slug=<?= urlencode((string) $log['project_slug']) ?>&tab=roadmap"
                               style="font-size:.85rem"><?= htmlspecialchars((string) ($log['project_title'] ?? 'Project #' . $log['project_id'])) ?></a>
                        <?php else: ?>
                            <span style="font-size:.85rem;color:var(--text-muted)">#<?= (int) ($log['project_id'] ?? 0) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) ($log['item_count'] ?? 0) ?></td>
                    <td>
                        <?php if ($log['success']): ?>
                            <span style="color:#22c55e;font-size:.8rem"><i class="fas fa-check-circle"></i> OK</span>
                        <?php else: ?>
                            <span style="color:#ef4444;font-size:.8rem"><i class="fas fa-times-circle"></i> <?= trans('admin_projects_error') ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars((string) ($log['api_contract_version'] ?? '—')) ?></td>
                    <td style="font-size:.75rem;color:#ef4444;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars((string) ($log['error_message'] ?? '')) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
