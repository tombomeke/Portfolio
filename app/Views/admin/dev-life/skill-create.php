<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <span class="card-title">Skill toevoegen</span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=skill-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label>Naam *</label>
                <input type="text" name="name" required autofocus placeholder="bijv. PHP, React, Docker">
            </div>
            <div class="form-group">
                <label>Categorie *</label>
                <select name="category" required>
                    <option value="">— Selecteer —</option>
                    <option value="languages">Languages</option>
                    <option value="frameworks">Frameworks</option>
                    <option value="database">Database</option>
                    <option value="tools">Tools</option>
                </select>
            </div>
            <div class="form-group">
                <label>Level</label>
                <select name="level">
                    <option value="1">1 – Beginner / Leren</option>
                    <option value="2">2 – Intermediate / Basis</option>
                    <option value="3">3 – Advanced / Goed</option>
                </select>
            </div>
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label>Notities</label>
                <input type="text" name="notes" placeholder="Korte beschrijving of context">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label>Gebruikt in projecten <span style="color:var(--text-muted)">– één per regel</span></label>
                <textarea name="projects" rows="4" placeholder="Portfolio Website&#10;RPG Manager"></textarea>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost">Annuleren</a>
        </div>
    </form>
</div>
