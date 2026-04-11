<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Categorie toevoegen</span>
        <a href="?page=admin&section=faq" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=faq&action=category-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label>Slug *</label>
                <input type="text" name="slug" required pattern="[a-z0-9\-]+" placeholder="bijv. algemeen">
                <span class="form-hint">Kleine letters, cijfers en streepjes.</span>
            </div>
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group">
                <label>Naam (NL) *</label>
                <input type="text" name="name_nl" required>
            </div>
            <div class="form-group">
                <label>Name (EN) *</label>
                <input type="text" name="name_en" required>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="?page=admin&section=faq" class="btn btn-ghost">Annuleren</a>
        </div>
    </form>
</div>
