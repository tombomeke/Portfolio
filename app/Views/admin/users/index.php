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
                    <td>
                        <span class="badge-status <?= $u['role'] ?>">
                            <?php if ($u['role'] === 'owner'): ?>
                                <?= trans('profile_role_owner') ?>
                            <?php elseif ($u['role'] === 'admin'): ?>
                                <?= trans('profile_role_admin') ?>
                            <?php else: ?>
                                <?= trans('profile_role_member') ?>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td><?= date('d-m-Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="user-actions">
                        <?php if ($u['role'] === 'user'): ?>
                        <form method="POST" action="?page=admin&section=users&action=promote&id=<?= $u['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" class="confirm-inline-neutral" data-confirm="<?= trans('admin_users_promote_confirm') ?> '<?= htmlspecialchars($u['username']) ?>'?">
                                <i class="fas fa-user-shield"></i> <?= trans('admin_users_make_admin') ?>
                            </button>
                        </form>
                        <?php elseif ($u['role'] === 'admin' && $u['id'] !== $authUser['id']): ?>
                        <form method="POST" action="?page=admin&section=users&action=demote&id=<?= $u['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" class="confirm-inline-neutral" data-confirm="<?= trans('admin_users_demote_confirm') ?> '<?= htmlspecialchars($u['username']) ?>'?">
                                <i class="fas fa-user-minus"></i> <?= trans('admin_users_make_user') ?>
                            </button>
                        </form>
                        <?php endif; ?>

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
                        </div>
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
    <p style="margin-top:.45rem;font-size:.8rem;color:var(--text-muted)">
        <i class="fas fa-user-shield"></i>
        <?= trans('admin_users_manage_roles_note') ?>
    </p>
</div>
