<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-tags"></i> Tags</span>
        <a href="?page=admin&section=tags&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nieuwe tag</a>
    </div>
    <?php if (empty($tags)): ?>
        <p class="empty-state"><i class="fas fa-tags"></i> Nog geen tags aangemaakt.</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>Tag</th><th>Slug</th><th style="width:80px">Gebruik</th><th style="width:120px"></th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
        <tr>
            <td><strong><?= htmlspecialchars($tag['name']) ?></strong></td>
            <td class="text-muted text-sm"><?= htmlspecialchars($tag['slug']) ?></td>
            <td class="text-sm"><?= (int)$tag['news_count'] ?> artikel<?= $tag['news_count'] !== 1 ? 'en' : '' ?></td>
            <td>
                <div style="display:flex;gap:.4rem">
                    <a href="?page=admin&section=tags&action=edit&id=<?= $tag['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i></a>
                    <form method="POST" action="?page=admin&section=tags&action=delete&id=<?= $tag['id'] ?>" style="display:inline">
                        <?= \Auth::csrfField() ?>
                        <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger)"
                                data-confirm="Tag '<?= htmlspecialchars(addslashes($tag['name'])) ?>' verwijderen? Dit verwijdert de tag van alle artikelen.">
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
