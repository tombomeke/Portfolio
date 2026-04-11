<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Tag toevoegen</span>
        <a href="?page=admin&section=tags" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=tags&action=create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem;padding:1.25rem">
            <div class="form-group">
                <label>Naam *</label>
                <input type="text" name="name" required autofocus placeholder="bijv. Laravel, Tutorial, Update">
            </div>
            <div class="form-group">
                <label>Slug <small style="font-weight:400">(optioneel — wordt automatisch gegenereerd)</small></label>
                <input type="text" name="slug" placeholder="bijv. laravel, tutorial, update">
                <span class="form-hint"><i class="fas fa-info-circle"></i> URL-vriendelijke identifier. Leeg laten voor automatische generatie.</span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Aanmaken</button>
                <a href="?page=admin&section=tags" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
