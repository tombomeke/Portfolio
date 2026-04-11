<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card" style="max-width:500px">
    <div class="card-header">
        <span class="card-title">Admin toevoegen</span>
        <a href="?page=admin&section=users" class="btn btn-ghost btn-sm">← Terug</a>
    </div>

    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.5rem">
        Nieuwe admin-accounts kunnen alle content beheren (news, FAQ, projecten, contact), maar hebben geen toegang tot gebruikersbeheer.
    </p>

    <form method="POST" action="?page=admin&section=users&action=create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem">
            <div class="form-group">
                <label>Gebruikersnaam *</label>
                <input type="text" name="username" required pattern="[a-zA-Z0-9_]{3,30}" autofocus>
                <span class="form-hint">3–30 tekens. Letters, cijfers, underscore.</span>
            </div>
            <div class="form-group">
                <label>E-mailadres *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Wachtwoord *</label>
                <input type="password" name="password" required minlength="8">
                <span class="form-hint">Minimaal 8 tekens.</span>
            </div>
            <div class="form-group">
                <label>Bevestig wachtwoord *</label>
                <input type="password" name="confirm" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Account aanmaken</button>
                <a href="?page=admin&section=users" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
