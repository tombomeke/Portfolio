<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Project toevoegen</span>
        <a href="?page=admin&section=projects" class="btn btn-ghost btn-sm">← Terug</a>
    </div>

    <form method="POST" action="?page=admin&section=projects&action=create" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.5rem">

            <div class="form-grid form-grid-2" style="gap:1rem">
                <div class="form-group">
                    <label>Slug *</label>
                    <input type="text" name="slug" required pattern="[a-zA-Z0-9\-]+" placeholder="bijv. rpg-manager">
                    <span class="form-hint">Unieke identifier voor het project.</span>
                </div>
                <div class="form-group">
                    <label>Categorie *</label>
                    <select name="category" required>
                        <option value="">— Selecteer —</option>
                        <option value="web">Web</option>
                        <option value="cli">CLI</option>
                        <option value="api">API</option>
                        <option value="minecraft">Minecraft</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">— Geen —</option>
                        <option value="active">Active</option>
                        <option value="development">Development</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Volgorde</label>
                    <input type="number" name="sort_order" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>GitHub URL</label>
                    <input type="url" name="repo_url" placeholder="https://github.com/...">
                </div>
                <div class="form-group">
                    <label>Demo URL</label>
                    <input type="url" name="demo_url" placeholder="https://...">
                </div>
            </div>

            <div class="form-group">
                <label>Technologieën (komma- of regelgescheiden)</label>
                <input type="text" name="tech" placeholder="PHP, JavaScript, MySQL">
            </div>

            <div class="form-group">
                <label>Afbeelding</label>
                <input type="file" name="image" accept="image/*">
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
                            <input type="text" name="title_nl" required>
                        </div>
                        <div class="form-group">
                            <label>Korte beschrijving (NL) *</label>
                            <textarea name="description_nl" required rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Uitgebreide beschrijving (NL) <span style="color:var(--text-muted)">(HTML toegestaan)</span></label>
                            <textarea name="long_description_nl" class="tall"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Features (NL) <span style="color:var(--text-muted)">– één per regel</span></label>
                            <textarea name="features_nl" rows="5" placeholder="Responsive design&#10;Dark theme&#10;NL/EN support"></textarea>
                        </div>
                    </div>
                </div>

                <div id="proj-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Title (EN) *</label>
                            <input type="text" name="title_en" required>
                        </div>
                        <div class="form-group">
                            <label>Short description (EN) *</label>
                            <textarea name="description_en" required rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Long description (EN) <span style="color:var(--text-muted)">(HTML allowed)</span></label>
                            <textarea name="long_description_en" class="tall"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Features (EN) <span style="color:var(--text-muted)">– one per line</span></label>
                            <textarea name="features_en" rows="5" placeholder="Responsive design&#10;Dark theme&#10;NL/EN support"></textarea>
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
