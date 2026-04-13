<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_faq_edit_item') ?> #<?= $item['id'] ?></span>
        <a href="?page=admin&section=faq" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>
    <form method="POST" action="?page=admin&section=faq&action=item-edit&id=<?= $item['id'] ?>">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem">
            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label><?= trans('admin_faq_category_required') ?></label>
                    <select name="faq_category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($item['faq_category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name_nl'] ?? $cat['slug']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= trans('admin_faq_order') ?></label>
                    <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>" min="0">
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
                            <label><?= trans('admin_faq_question_nl_required') ?></label>
                            <input type="text" name="question_nl" value="<?= htmlspecialchars($item['question_nl'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= trans('admin_faq_answer_nl_required') ?></label>
                            <textarea name="answer_nl" required><?= htmlspecialchars($item['answer_nl'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div id="faq-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Question (EN) *</label>
                            <input type="text" name="question_en" value="<?= htmlspecialchars($item['question_en'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Answer (EN) *</label>
                            <textarea name="answer_en" required><?= htmlspecialchars($item['answer_en'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
                <a href="?page=admin&section=faq" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
        </div>
    </form>
</div>
