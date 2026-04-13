<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_contact_messages') ?> (<?= count($messages) ?>)</span>
    </div>

    <?php if (empty($messages)): ?>
        <p style="color:var(--text-muted)"><?= trans('admin_contact_no_messages') ?></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><?= trans('admin_table_from') ?></th>
                    <th><?= trans('contact_email') ?></th>
                    <th><?= trans('admin_table_status') ?></th>
                    <th><?= trans('admin_contact_replied') ?></th>
                    <th><?= trans('admin_received_label') ?></th>
                    <th><?= trans('admin_actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr>
                    <td>
                        <?php if (!$m['read_at']): ?>
                            <strong><?= htmlspecialchars($m['name']) ?></strong>
                        <?php else: ?>
                            <?= htmlspecialchars($m['name']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td>
                        <?php if (!$m['read_at']): ?>
                            <span class="badge-status unread"><?= trans('admin_stats_unread') ?></span>
                        <?php else: ?>
                            <span class="badge-status read"><?= trans('admin_contact_read') ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $m['replied_at'] ? '<span style="color:var(--success)"><i class="fas fa-check"></i> ' . htmlspecialchars(trans('admin_contact_yes')) . '</span>' : '—' ?>
                    </td>
                    <td><?= date('d-m-Y H:i', strtotime($m['created_at'])) ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=contact&action=show&id=<?= $m['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=contact&action=delete&id=<?= $m['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="<?= trans('admin_contact_delete_confirm') ?>">
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
