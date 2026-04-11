<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap">
    <a href="?page=admin&section=dev-life&action=skill-create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Skill toevoegen</a>
    <a href="?page=admin&section=dev-life&action=edu-create"   class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i> Opleiding toevoegen</a>
    <a href="?page=admin&section=dev-life&action=goal-create"  class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i> Leerdoel toevoegen</a>
</div>

<!-- Skills -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title">Skills (<?= count($skillList) ?>)</span>
        <a href="?page=admin&section=dev-life&action=skill-create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i></a>
    </div>
    <?php if (empty($skillList)): ?>
        <p style="color:var(--text-muted)">Nog geen skills.</p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Naam</th><th>Categorie</th><th>Level</th><th>Volgorde</th><th>Acties</th></tr></thead>
            <tbody>
                <?php foreach ($skillList as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><code style="font-size:.8rem"><?= htmlspecialchars($s['category']) ?></code></td>
                    <td>
                        <?php $levels = ['', 'Beginner', 'Intermediate', 'Advanced']; ?>
                        <span class="badge-status <?= $s['level'] == 3 ? 'active' : ($s['level'] == 2 ? 'development' : 'draft') ?>">
                            <?= $levels[$s['level']] ?? $s['level'] ?>
                        </span>
                    </td>
                    <td><?= $s['sort_order'] ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=dev-life&action=skill-edit&id=<?= $s['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-pencil"></i></a>
                        <form method="POST" action="?page=admin&section=dev-life&action=skill-delete&id=<?= $s['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Skill '<?= htmlspecialchars($s['name']) ?>' verwijderen?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Education -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title">Opleidingen & Certificaten (<?= count($education) ?>)</span>
        <a href="?page=admin&section=dev-life&action=edu-create" class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i></a>
    </div>
    <?php if (empty($education)): ?>
        <p style="color:var(--text-muted)">Nog geen opleidingen. <a href="?page=admin&section=dev-life&action=edu-create" style="color:var(--primary)">Voeg er een toe.</a></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Titel (NL)</th><th>Instelling</th><th>Periode</th><th>Acties</th></tr></thead>
            <tbody>
                <?php foreach ($education as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['title_nl'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['institution_nl'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['period_nl'] ?? '—') ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=dev-life&action=edu-edit&id=<?= $e['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-pencil"></i></a>
                        <form method="POST" action="?page=admin&section=dev-life&action=edu-delete&id=<?= $e['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Opleiding verwijderen?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Learning Goals -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Leerdoelen (<?= count($goals) ?>)</span>
        <a href="?page=admin&section=dev-life&action=goal-create" class="btn btn-ghost btn-sm"><i class="fas fa-plus"></i></a>
    </div>
    <?php if (empty($goals)): ?>
        <p style="color:var(--text-muted)">Nog geen leerdoelen. <a href="?page=admin&section=dev-life&action=goal-create" style="color:var(--primary)">Voeg er een toe.</a></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Titel (NL)</th><th>Voortgang</th><th>Volgorde</th><th>Acties</th></tr></thead>
            <tbody>
                <?php foreach ($goals as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['title_nl'] ?? '—') ?></td>
                    <td><?= $g['progress'] ?>%</td>
                    <td><?= $g['sort_order'] ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=dev-life&action=goal-edit&id=<?= $g['id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-pencil"></i></a>
                        <form method="POST" action="?page=admin&section=dev-life&action=goal-delete&id=<?= $g['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Leerdoel verwijderen?"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
