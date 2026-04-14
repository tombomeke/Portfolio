<?php // admin/projects/roadmap-items.php — manual done/open toggle for DB roadmap items ?>

<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            Roadmap items &mdash;
            <a href="?page=admin&section=projects&action=edit&id=<?= (int) $project['id'] ?>" style="color:var(--primary)">
                <?= htmlspecialchars((string) ($project['title_nl'] ?? $project['slug'])) ?>
            </a>
        </span>
        <a href="?page=admin&section=projects" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Terug
        </a>
    </div>

    <?php if (empty($items)): ?>
        <p style="color:var(--text-muted);padding:1rem">
            Geen roadmap items gevonden. <a href="?page=admin&section=projects&action=edit&id=<?= (int) $project['id'] ?>" style="color:var(--primary)">Sync eerst een roadmap</a>.
        </p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th style="width:4rem">Status</th>
                    <th>Bestand</th>
                    <th style="width:3.5rem">Regel</th>
                    <th>Tekst</th>
                    <th style="width:5rem">Prioriteit</th>
                    <th style="width:7rem">Actie</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                    $isDone   = ($item['status'] ?? 'open') === 'done';
                    $priority = (string) ($item['priority'] ?? 'normal');
                ?>
                <tr style="<?= $isDone ? 'opacity:.55;' : '' ?>">
                    <td>
                        <?php if ($isDone): ?>
                            <span class="badge badge-success">done</span>
                        <?php else: ?>
                            <span class="badge badge-muted">open</span>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:.8rem"><?= htmlspecialchars((string) ($item['file'] ?? '')) ?></code></td>
                    <td style="text-align:right"><?= (int) ($item['line'] ?? 0) ?></td>
                    <td style="font-size:.875rem"><?= htmlspecialchars((string) ($item['text'] ?? '')) ?></td>
                    <td>
                        <?php if ($priority === 'high'): ?>
                            <span class="badge badge-error">high</span>
                        <?php elseif ($priority === 'medium'): ?>
                            <span class="badge badge-warning">medium</span>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.8rem"><?= htmlspecialchars($priority) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST"
                              action="?page=admin&section=project-roadmap-items&action=toggle&id=<?= (int) $item['id'] ?>&project_id=<?= (int) $projectId ?>"
                              style="display:inline">
                            <?= \Auth::csrfField() ?>
                            <input type="hidden" name="status" value="<?= $isDone ? 'open' : 'done' ?>">
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <?= $isDone ? '<i class="fas fa-rotate-left"></i> Heropen' : '<i class="fas fa-check"></i> Done' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="color:var(--text-muted);font-size:.8rem;padding:.5rem 1rem">
        <?= count($items) ?> items &mdash;
        <?= count(array_filter($items, fn($i) => ($i['status'] ?? '') === 'done')) ?> done,
        <?= count(array_filter($items, fn($i) => ($i['status'] ?? 'open') !== 'done')) ?> open.
        Handmatig op &ldquo;done&rdquo; gezette items blijven bewaard na de volgende sync.
    </p>
    <?php endif; ?>
</div>
