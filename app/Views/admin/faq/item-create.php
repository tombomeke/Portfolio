<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">FAQ-item toevoegen</span>
        <a href="?page=admin&section=faq" class="btn btn-ghost btn-sm">← Terug</a>
    </div>
    <form method="POST" action="?page=admin&section=faq&action=item-create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem">
            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label>Categorie *</label>
                    <select name="faq_category_id" required>
                        <option value="">— Selecteer —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($catId === $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name_nl'] ?? $cat['slug']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Volgorde</label>
                    <input type="number" name="sort_order" value="0" min="0">
                </div>
            </div>

            <div>
                <div class="lang-tabs lang-tab-group">
                    <button type="button" class="lang-tab active" data-target="faq-nl">🇳🇱 Nederlands</button>
                    <button type="button" class="lang-tab" data-target="faq-en">🇬🇧 English</button>
                </div>
                <div id="faq-nl" class="lang-panel active">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Vraag (NL) *</label>
                            <input type="text" name="question_nl" required>
                        </div>
                        <div class="form-group">
                            <label>Antwoord (NL) *</label>
                            <textarea name="answer_nl" required></textarea>
                        </div>
                    </div>
                </div>
                <div id="faq-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Question (EN) *</label>
                            <input type="text" name="question_en" required>
                        </div>
                        <div class="form-group">
                            <label>Answer (EN) *</label>
                            <textarea name="answer_en" required></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
                <a href="?page=admin&section=faq" class="btn btn-ghost">Annuleren</a>
            </div>
        </div>
    </form>
</div>
