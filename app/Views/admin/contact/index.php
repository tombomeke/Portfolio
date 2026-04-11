<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Contact berichten (<?= count($messages) ?>)</span>
    </div>

    <?php if (empty($messages)): ?>
        <p style="color:var(--text-muted)">Geen berichten ontvangen.</p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Van</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th>Beantwoord</th>
                    <th>Ontvangen</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr>
                    <td>
                        <?php if (!$m['read_at']): ?>
                            <strong><?= htmlspecialchars($m['name']) ?></strong>
                        <?php else: ?>
                            <?= htmlspecialchars($m['name']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td>
                        <?php if (!$m['read_at']): ?>
                            <span class="badge-status unread">Ongelezen</span>
                        <?php else: ?>
                            <span class="badge-status read">Gelezen</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $m['replied_at'] ? '<span style="color:var(--success)"><i class="fas fa-check"></i> Ja</span>' : '—' ?>
                    </td>
                    <td><?= date('d-m-Y H:i', strtotime($m['created_at'])) ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=admin&section=contact&action=show&id=<?= $m['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=contact&action=delete&id=<?= $m['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Bericht verwijderen?">
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
