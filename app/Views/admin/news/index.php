<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Alle nieuwsberichten (<?= count($items) ?>)</span>
        <a href="?page=admin&section=news&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Toevoegen
        </a>
    </div>

    <?php if (empty($items)): ?>
        <p style="color:var(--text-muted)">Nog geen nieuwsberichten.</p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel (NL)</th>
                    <th>Status</th>
                    <th>Gepubliceerd</th>
                    <th>Aangemaakt</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= htmlspecialchars($item['title_nl'] ?? '(geen NL titel)') ?></td>
                    <td>
                        <?php if ($item['published_at'] && strtotime($item['published_at']) <= time()): ?>
                            <span class="badge-status published">Gepubliceerd</span>
                        <?php else: ?>
                            <span class="badge-status draft">Concept</span>
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
                            <button type="submit" data-confirm="Nieuwsbericht verwijderen?">
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
