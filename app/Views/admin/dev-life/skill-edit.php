<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<?php $projectsStr = implode("\n", $skill['projects'] ?? []); ?>
<div class="card">
    <div class="card-header">
        <span class="card-title">Skill bewerken: <?= htmlspecialchars($skill['name']) ?></span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=skill-edit&id=<?= $skill['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem">
            <div class="form-group">
                <label>Naam *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($skill['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Categorie *</label>
                <select name="category" required>
                    <?php foreach (['languages','frameworks','database','tools'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $skill['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Level</label>
                <select name="level">
                    <?php foreach ([1=>'1 – Beginner',2=>'2 – Intermediate',3=>'3 – Advanced'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $skill['level'] == $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="<?= $skill['sort_order'] ?>" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label>Notities</label>
                <input type="text" name="notes" value="<?= htmlspecialchars($skill['notes'] ?? '') ?>">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label>Gebruikt in projecten – één per regel</label>
                <textarea name="projects" rows="4"><?= htmlspecialchars($projectsStr) ?></textarea>
            </div>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost">Annuleren</a>
        </div>
    </form>
</div>
