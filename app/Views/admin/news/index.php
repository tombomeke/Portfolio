<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_news_all_posts') ?> (<?= count($items) ?>)</span>
        <a href="?page=admin&section=news&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> <?= trans('admin_add') ?>
        </a>
    </div>

    <?php if (empty($items)): ?>
        <p style="color:var(--text-muted)"><?= trans('admin_no_news_yet') ?></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= trans('admin_news_title_nl') ?></th>
                    <th><?= trans('admin_table_status') ?></th>
                    <th><?= trans('admin_news_published_at') ?></th>
                    <th><?= trans('admin_news_created_at') ?></th>
                    <th><?= trans('admin_actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['title_nl'] ?? trans('admin_news_no_nl_title')) ?></td>
                    <td>
                        <?php if ($item['published_at'] && strtotime($item['published_at']) <= time()): ?>
                            <span class="badge-status published"><?= trans('admin_status_published') ?></span>
                        <?php else: ?>
                            <span class="badge-status draft"><?= trans('admin_status_draft') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $item['published_at'] ? date('d-m-Y H:i', strtotime($item['published_at'])) : '—' ?></td>
                    <td><?= date('d-m-Y', strtotime($item['created_at'])) ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=news&action=edit&id=<?= $item['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=news&action=delete&id=<?= $item['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="<?= trans('admin_news_delete_confirm') ?>">
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
