<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-tags"></i> Tags</span>
        <a href="?page=admin&section=tags&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> <?= trans('admin_tags_new') ?></a>
    </div>
    <?php if (empty($tags)): ?>
        <p class="empty-state"><i class="fas fa-tags"></i> <?= trans('admin_tags_none') ?></p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>Tag</th><th>Slug</th><th style="width:80px"><?= trans('admin_tags_usage') ?></th><th style="width:120px"></th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
        <tr>
            <td><strong><?= htmlspecialchars($tag['name']) ?></strong></td>
            <td class="text-muted text-sm"><?= htmlspecialchars($tag['slug']) ?></td>
            <td class="text-sm"><?= (int)$tag['news_count'] ?> <?= trans('admin_tags_articles') ?><?= $tag['news_count'] !== 1 ? trans('admin_tags_articles_suffix') : '' ?></td>
            <td>
                <div style="display:flex;gap:.4rem">
                    <a href="?page=admin&section=tags&action=edit&id=<?= $tag['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i></a>
                    <form method="POST" action="?page=admin&section=tags&action=delete&id=<?= $tag['id'] ?>" style="display:inline">
                        <?= \Auth::csrfField() ?>
                        <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger)"
                                data-confirm="<?= trans('admin_tags_delete_confirm_prefix') ?> '<?= htmlspecialchars(addslashes($tag['name'])) ?>'? <?= trans('admin_tags_delete_confirm_suffix') ?>">
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
