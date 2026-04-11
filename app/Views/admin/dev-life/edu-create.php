<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <span class="card-title">Opleiding / Certificaat toevoegen</span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=edu-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;margin-bottom:1.25rem">
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="0" min="0">
            </div>
            <div class="form-group">
                <label>Certificaat URL (optioneel)</label>
                <input type="url" name="certificate_url" placeholder="https://...">
            </div>
        </div>
        <div>
            <div class="lang-tabs lang-tab-group">
                <button type="button" class="lang-tab active" data-target="edu-nl">🇳🇱 Nederlands</button>
                <button type="button" class="lang-tab" data-target="edu-en">🇬🇧 English</button>
            </div>
            <?php foreach ([['nl','NL','Instelling','Periode','Omschrijving'],['en','EN','Institution','Period','Description']] as [$lang,$label,$inst,$per,$desc]): ?>
            <div id="edu-<?= $lang ?>" class="lang-panel <?= $lang === 'nl' ? 'active' : '' ?>">
                <div class="form-grid" style="gap:1rem">
                    <div class="form-group"><label>Titel (<?= $label ?>) *</label><input type="text" name="title_<?= $lang ?>" <?= $lang === 'nl' ? 'required' : '' ?>></div>
                    <div class="form-group form-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group"><label><?= $inst ?></label><input type="text" name="institution_<?= $lang ?>" placeholder="bijv. HvA"></div>
                        <div class="form-group"><label><?= $per ?></label><input type="text" name="period_<?= $lang ?>" placeholder="2023 – heden"></div>
                    </div>
                    <div class="form-group"><label><?= $desc ?></label><textarea name="description_<?= $lang ?>"></textarea></div>
                    <div class="form-group"><label>Skills/vakken – één per regel</label><textarea name="skills_<?= $lang ?>" rows="4" placeholder="Software Architectuur&#10;Team Collaboration"></textarea></div>
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
