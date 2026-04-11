<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Nieuwsbericht bewerken #<?= $item['id'] ?></span>
        <a href="?page=admin&section=news" class="btn btn-ghost btn-sm">← Terug</a>
    </div>

    <form method="POST" action="?page=admin&section=news&action=edit&id=<?= $item['id'] ?>" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>

        <div class="form-grid" style="gap:1.5rem">

            <div class="form-group">
                <label>Publicatiedatum (leeg = concept)</label>
                <input type="datetime-local" name="published_at"
                       value="<?= $item['published_at'] ? date('Y-m-d\TH:i', strtotime($item['published_at'])) : '' ?>">
            </div>

            <div class="form-group">
                <label>Afbeelding</label>
                <?php if ($item['image_path']): ?>
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="img-preview" alt="Huidige afbeelding">
                    <label style="margin-top:.5rem;display:flex;align-items:center;gap:.4rem;font-size:.8rem">
                        <input type="checkbox" name="remove_image" value="1"> Afbeelding verwijderen
                    </label>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" style="margin-top:.5rem">
            </div>

            <div>
                <div class="lang-tabs lang-tab-group">
                    <button type="button" class="lang-tab active" data-target="lang-nl">🇳🇱 Nederlands</button>
                    <button type="button" class="lang-tab" data-target="lang-en">🇬🇧 English</button>
                </div>

                <div id="lang-nl" class="lang-panel active">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Titel (NL) *</label>
                            <input type="text" name="title_nl" value="<?= htmlspecialchars($item['title_nl'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Inhoud (NL) *</label>
                            <textarea name="content_nl" class="tall" required><?= htmlspecialchars($item['content_nl'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="lang-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Title (EN) *</label>
                            <input type="text" name="title_en" value="<?= htmlspecialchars($item['title_en'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Content (EN) *</label>
                            <textarea name="content_en" class="tall" required><?= htmlspecialchars($item['content_en'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
                <a href="?page=admin&section=news" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
