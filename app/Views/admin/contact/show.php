<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="display:flex;gap:1rem;align-items:flex-start">
    <div style="flex:1">
        <div class="card" style="margin-bottom:1rem">
            <div class="card-header">
                <span class="card-title">Bericht van <?= htmlspecialchars($message['name']) ?></span>
                <a href="?page=admin&section=contact" class="btn btn-ghost btn-sm">← Terug</a>
            </div>

            <div style="display:grid;gap:.5rem;margin-bottom:1.5rem;font-size:.875rem">
                <div><strong>Van:</strong> <?= htmlspecialchars($message['name']) ?></div>
                <div><strong>E-mail:</strong> <a href="mailto:<?= htmlspecialchars($message['email']) ?>" style="color:var(--primary)"><?= htmlspecialchars($message['email']) ?></a></div>
                <?php if ($message['subject']): ?>
                <div><strong>Onderwerp:</strong> <?= htmlspecialchars($message['subject']) ?></div>
                <?php endif; ?>
                <div><strong>Ontvangen:</strong> <?= date('d-m-Y H:i', strtotime($message['created_at'])) ?></div>
            </div>

            <div style="background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:1.25rem;white-space:pre-wrap;line-height:1.7;font-size:.9rem"><?= htmlspecialchars($message['message']) ?></div>
        </div>

        <?php if ($message['admin_reply']): ?>
        <div class="card" style="margin-bottom:1rem;border-color:rgba(34,197,94,.3)">
            <div class="card-header">
                <span class="card-title" style="color:var(--success)"><i class="fas fa-reply"></i> Jouw antwoord</span>
                <small style="color:var(--text-muted)"><?= date('d-m-Y H:i', strtotime($message['replied_at'])) ?></small>
            </div>
            <div style="white-space:pre-wrap;font-size:.9rem;line-height:1.7"><?= htmlspecialchars($message['admin_reply']) ?></div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><?= $message['admin_reply'] ? 'Opnieuw antwoorden' : 'Antwoorden' ?></span>
            </div>
            <form method="POST" action="?page=admin&section=contact&action=reply&id=<?= $message['id'] ?>">
                <?= \Auth::csrfField() ?>
                <div class="form-group" style="margin-bottom:1rem">
                    <label>Bericht naar <?= htmlspecialchars($message['email']) ?></label>
                    <textarea name="reply" class="tall" required placeholder="Schrijf je antwoord..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Versturen & opslaan</button>
                </div>
            </form>
        </div>
    </div>

    <div style="width:240px;flex-shrink:0">
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem">Acties</div>
            <form method="POST" action="?page=admin&section=contact&action=delete&id=<?= $message['id'] ?>">
                <?= \Auth::csrfField() ?>
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center" data-confirm="Bericht definitief verwijderen?">
                    <i class="fas fa-trash"></i> Verwijderen
                </button>
            </form>
        </div>
    </div>
</div>
