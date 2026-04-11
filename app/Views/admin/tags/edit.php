<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Tag bewerken: <strong><?= htmlspecialchars($tag['name']) ?></strong></span>
        <a href="?page=admin&section=tags" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=tags&action=edit&id=<?= $tag['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem;padding:1.25rem">
            <div class="form-group">
                <label>Naam *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($tag['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Slug <small style="font-weight:400">(optioneel)</small></label>
                <input type="text" name="slug" value="<?= htmlspecialchars($tag['slug']) ?>">
                <span class="form-hint"><i class="fas fa-info-circle"></i> Leeg laten om automatisch te genereren vanuit de naam.</span>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
                <a href="?page=admin&section=tags" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
