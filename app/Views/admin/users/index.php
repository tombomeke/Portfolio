<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_users') ?> (<?= count($userList) ?>)</span>
        <a href="?page=admin&section=users&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> <?= trans('admin_users_add_admin') ?>
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= trans('profile_username') ?></th>
                    <th><?= trans('contact_email') ?></th>
                    <th><?= trans('admin_users_role') ?></th>
                    <th><?= trans('admin_news_created_at') ?></th>
                    <th><?= trans('admin_actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge-status <?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                    <td><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['role'] !== 'owner' && $u['id'] !== $authUser['id']): ?>
                        <form method="POST" action="?page=admin&section=users&action=delete&id=<?= $u['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="<?= trans('admin_users_delete_confirm') ?> '<?= htmlspecialchars($u['username']) ?>'?">
                                <i class="fas fa-trash"></i> <?= trans('admin_action_delete') ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <span style="color:var(--text-muted);font-size:.8rem">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top:1rem;font-size:.8rem;color:var(--text-muted)">
        <i class="fas fa-info-circle"></i>
        <?= trans('admin_users_owner_note') ?>
    </p>
</div>
