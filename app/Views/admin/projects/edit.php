<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php
$techStr     = implode(', ', $project['tech'] ?? []);
$featuresNl  = implode("\n", $project['features_nl'] ?? []);
$featuresEn  = implode("\n", $project['features_en'] ?? []);
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Project bewerken: <?= htmlspecialchars($project['title_nl'] ?? '#'.$project['id']) ?></span>
        <a href="?page=admin&section=projects" class="btn btn-ghost btn-sm">← Terug</a>
    </div>

    <form method="POST" action="?page=admin&section=projects&action=edit&id=<?= $project['id'] ?>" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.5rem">

            <div class="form-grid form-grid-2" style="gap:1rem">
                <div class="form-group">
                    <label>Slug *</label>
                    <input type="text" name="slug" value="<?= htmlspecialchars($project['slug']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Categorie *</label>
                    <select name="category" required>
                        <?php foreach (['web','cli','api','minecraft'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= $project['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">— Geen —</option>
                        <?php foreach (['active','development','completed'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($project['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Volgorde</label>
                    <input type="number" name="sort_order" value="<?= $project['sort_order'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="repo_url" value="<?= htmlspecialchars($project['repo_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Demo URL</label>
                    <input type="url" name="demo_url" value="<?= htmlspecialchars($project['demo_url'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Technologieën</label>
                <input type="text" name="tech" value="<?= htmlspecialchars($techStr) ?>">
            </div>

            <div class="form-group">
                <label>Cover afbeelding</label>
                <?php if ($project['image_path']): ?>
                    <img src="<?= htmlspecialchars($project['image_path']) ?>" class="img-preview" alt="">
                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;margin-top:.5rem">
                        <input type="checkbox" name="remove_image" value="1"> Cover afbeelding verwijderen
                    </label>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" style="margin-top:.5rem">
                <span class="form-hint">Primaire afbeelding voor de projectkaart.</span>
            </div>

            <?php
            $galleryImages = $galleryImages ?? [];
            ?>
            <div class="form-group">
                <label>Galerij afbeeldingen</label>
                <?php if (!empty($galleryImages)): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:.75rem">
                        <?php foreach ($galleryImages as $img): ?>
                            <div style="position:relative;display:inline-block">
                                <img src="<?= htmlspecialchars((string) $img['image_path']) ?>"
                                     style="width:100px;height:75px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color)" alt="">
                                <label style="display:flex;align-items:center;gap:.25rem;font-size:.75rem;margin-top:.25rem">
                                    <input type="checkbox" name="delete_images[]" value="<?= (int) $img['id'] ?>">
                                    Verwijderen
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.5rem">Nog geen extra afbeeldingen.</p>
                <?php endif; ?>
                <input type="file" name="gallery_images[]" accept="image/*" multiple>
                <span class="form-hint">Selecteer meerdere bestanden om toe te voegen aan de carousel.</span>
            </div>

            <div>
                <div class="lang-tabs lang-tab-group">
                    <button type="button" class="lang-tab active" data-target="proj-nl">🇳🇱 Nederlands</button>
                    <button type="button" class="lang-tab" data-target="proj-en">🇬🇧 English</button>
                </div>

                <div id="proj-nl" class="lang-panel active">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Titel (NL) *</label>
                            <input type="text" name="title_nl" value="<?= htmlspecialchars($project['title_nl'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Korte beschrijving (NL) *</label>
                            <textarea name="description_nl" required rows="3"><?= htmlspecialchars($project['desc_nl'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Uitgebreide beschrijving (NL)</label>
                            <textarea name="long_description_nl" class="tall"><?= htmlspecialchars($project['long_desc_nl'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Features (NL) – één per regel</label>
                            <textarea name="features_nl" rows="5"><?= htmlspecialchars($featuresNl) ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="proj-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Title (EN) *</label>
                            <input type="text" name="title_en" value="<?= htmlspecialchars($project['title_en'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Short description (EN) *</label>
                            <textarea name="description_en" required rows="3"><?= htmlspecialchars($project['desc_en'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Long description (EN)</label>
                            <textarea name="long_description_en" class="tall"><?= htmlspecialchars($project['long_desc_en'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Features (EN) – one per line</label>
                            <textarea name="features_en" rows="5"><?= htmlspecialchars($featuresEn) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
                <a href="?page=admin&section=projects" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
