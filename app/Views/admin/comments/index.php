<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-comments"></i> <?= trans('admin_comments_moderate_title') ?></span>
    </div>
    <?php if (empty($comments)): ?>
        <p class="empty-state"><i class="far fa-comment-dots"></i> <?= trans('admin_comments_none') ?></p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th><?= trans('admin_table_article') ?></th>
                <th><?= trans('admin_table_author') ?></th>
                <th><?= trans('admin_table_comment') ?></th>
                <th style="width:110px"><?= trans('admin_table_date') ?></th>
                <th style="width:90px"><?= trans('admin_table_status') ?></th>
                <th style="width:110px"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($comments as $c): ?>
        <tr>
            <td>
                <a href="?page=news-item&id=<?= (int)$c['news_id'] ?>" target="_blank" class="text-sm" style="color:var(--primary)">
                    <?= htmlspecialchars(mb_strimwidth($c['news_title'] ?? '—', 0, 35, '…')) ?>
                </a>
            </td>
            <td><strong><?= htmlspecialchars($c['username'] ?? '?') ?></strong></td>
            <td class="text-sm"><?= htmlspecialchars(mb_strimwidth($c['body'], 0, 100, '…')) ?></td>
            <td class="text-muted text-sm"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
            <td>
                <?php if ($c['is_approved']): ?>
                    <span class="badge badge--success"><?= trans('admin_comments_approved') ?></span>
                <?php else: ?>
                    <span class="badge badge--warning"><?= trans('admin_comments_pending') ?></span>
                <?php endif; ?>
            </td>
            <td>
                <div style="display:flex;gap:.3rem">
                    <?php if (!$c['is_approved']): ?>
                    <form method="POST" action="?page=admin&section=comments&action=approve&id=<?= $c['id'] ?>">
                        <?= \Auth::csrfField() ?>
                        <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--success)" title="<?= trans('admin_action_approve') ?>">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="?page=admin&section=comments&action=delete&id=<?= $c['id'] ?>">
                        <?= \Auth::csrfField() ?>
                        <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger)"
                                data-confirm="<?= trans('admin_comments_delete_confirm') ?> <?= htmlspecialchars(addslashes($c['username'] ?? '?')) ?>?" title="<?= trans('admin_action_delete') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
