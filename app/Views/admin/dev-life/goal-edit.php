<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php
// Format resources back to "Name | url" per line
function formatResources(?string $json): string {
    $items = json_decode($json ?? '[]', true) ?? [];
    return implode("\n", array_map(fn($r) => is_array($r) ? trim($r['name'].' | '.($r['url']??''), ' |') : $r, $items));
}
$resNl = formatResources($goal['resources_nl'] ?? null);
$resEn = formatResources($goal['resources_en'] ?? null);
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Leerdoel bewerken</span>
        <a href="?page=admin&section=dev-life" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=dev-life&action=goal-edit&id=<?= $goal['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;margin-bottom:1.25rem">
            <div class="form-group">
                <label>Voortgang (0–100%)</label>
                <input type="number" name="progress" value="<?= $goal['progress'] ?>" min="0" max="100">
            </div>
            <div class="form-group">
                <label>Volgorde</label>
                <input type="number" name="sort_order" value="<?= $goal['sort_order'] ?>" min="0">
            </div>
        </div>
        <div>
            <div class="lang-tabs lang-tab-group">
                <button type="button" class="lang-tab active" data-target="goal-nl">🇳🇱 Nederlands</button>
                <button type="button" class="lang-tab" data-target="goal-en">🇬🇧 English</button>
            </div>
            <?php foreach (['nl' => $resNl, 'en' => $resEn] as $lang => $resStr): ?>
            <div id="goal-<?= $lang ?>" class="lang-panel <?= $lang === 'nl' ? 'active' : '' ?>">
                <div class="form-grid" style="gap:1rem">
                    <div class="form-group"><label>Titel *</label><input type="text" name="title_<?= $lang ?>" value="<?= htmlspecialchars($goal["title_{$lang}"] ?? '') ?>" <?= $lang === 'nl' ? 'required' : '' ?>></div>
                    <div class="form-group"><label>Beschrijving</label><textarea name="description_<?= $lang ?>"><?= htmlspecialchars($goal["description_{$lang}"] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Tijdlijn</label><input type="text" name="timeline_<?= $lang ?>" value="<?= htmlspecialchars($goal["timeline_{$lang}"] ?? '') ?>"></div>
                    <div class="form-group">
                        <label>Bronnen – één per regel: <code>Naam | https://url</code></label>
                        <textarea name="resources_<?= $lang ?>" rows="4"><?= htmlspecialchars($resStr) ?></textarea>
                    </div>
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
