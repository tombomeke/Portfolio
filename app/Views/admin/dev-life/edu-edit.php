<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<?php
$skillsNlStr = implode("\n", $item['skills_nl'] ?? []);
$skillsEnStr = implode("\n", $item['skills_en'] ?? []);
?>
<div class="card">
    <div class="card-header">
        <span class="card-title">Opleiding bewerken</span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=edu-edit&id=<?= $item['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;margin-bottom:1.25rem">
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>" min="0">
            </div>
            <div class="form-group">
                <label>Certificaat URL</label>
                <input type="url" name="certificate_url" value="<?= htmlspecialchars($item['certificate_url'] ?? '') ?>">
            </div>
        </div>
        <div>
            <div class="lang-tabs lang-tab-group">
                <button type="button" class="lang-tab active" data-target="edu-nl">🇳🇱 Nederlands</button>
                <button type="button" class="lang-tab" data-target="edu-en">🇬🇧 English</button>
            </div>
            <?php foreach (['nl' => $skillsNlStr, 'en' => $skillsEnStr] as $lang => $skillsStr): ?>
            <div id="edu-<?= $lang ?>" class="lang-panel <?= $lang === 'nl' ? 'active' : '' ?>">
                <div class="form-grid" style="gap:1rem">
                    <div class="form-group"><label>Titel *</label><input type="text" name="title_<?= $lang ?>" value="<?= htmlspecialchars($item["title_{$lang}"] ?? '') ?>" <?= $lang === 'nl' ? 'required' : '' ?>></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group"><label>Instelling</label><input type="text" name="institution_<?= $lang ?>" value="<?= htmlspecialchars($item["institution_{$lang}"] ?? '') ?>"></div>
                        <div class="form-group"><label>Periode</label><input type="text" name="period_<?= $lang ?>" value="<?= htmlspecialchars($item["period_{$lang}"] ?? '') ?>"></div>
                    </div>
                    <div class="form-group"><label>Omschrijving</label><textarea name="description_<?= $lang ?>"><?= htmlspecialchars($item["description_{$lang}"] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Skills/vakken – één per regel</label><textarea name="skills_<?= $lang ?>" rows="4"><?= htmlspecialchars($skillsStr) ?></textarea></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions" style="margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            <a href="?page=admin&section=dev-life" class="btn btn-ghost">Annuleren</a>
        </div>
    </form>
</div>
