<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Alle projecten (<?= count($projects) ?>)</span>
        <div style="display:flex;gap:.4rem;align-items:center">
            <form method="POST" action="?page=admin&section=projects&action=sync-all"
                  onsubmit="return confirm('Alle project-roadmaps synchroniseren? Dit kan even duren.')">
                <?= \Auth::csrfField() ?>
                <button type="submit" class="btn btn-ghost btn-sm" title="Alle roadmaps syncen via ReadmeSync API">
                    <i class="fas fa-rotate"></i> Sync roadmaps
                </button>
            </form>
            <a href="?page=admin&section=projects&action=create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Toevoegen
            </a>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <p style="color:var(--text-muted)">Nog geen projecten. <a href="?page=admin&section=projects&action=create" style="color:var(--primary)">Voeg er een toe.</a></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel (NL)</th>
                    <th>Categorie</th>
                    <th>Status</th>
                    <th>Volgorde</th>
                    <th>Acties</th>
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
                        <a href="?page=project&slug=<?= urlencode((string) ($p['slug'] ?? '')) ?>&tab=roadmap" class="btn btn-ghost btn-sm" title="Open publieke roadmap">
                            <i class="fas fa-list-check"></i>
                        </a>
                        <a href="?page=admin&section=projects&action=edit&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=projects&action=delete&id=<?= $p['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Project verwijderen?">
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
