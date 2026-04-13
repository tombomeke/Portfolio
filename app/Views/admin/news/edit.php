<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_news_edit_post') ?> #<?= $item['id'] ?></span>
        <a href="?page=admin&section=news" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>

    <form method="POST" action="?page=admin&section=news&action=edit&id=<?= $item['id'] ?>" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>

        <div class="form-grid" style="gap:1.5rem">

            <div class="form-group">
                <label><?= trans('admin_news_publish_mode_label') ?></label>
                <p class="form-hint" style="margin:0">
                    <?= trans('admin_news_publish_mode_hint') ?>
                </p>
                <?php if (!empty($item['published_at'])): ?>
                    <small class="text-muted" style="display:block;margin-top:.35rem">
                        <?= trans('admin_news_published_at') ?>: <?= htmlspecialchars(date('d-m-Y H:i', strtotime($item['published_at']))) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label><?= trans('admin_news_image') ?></label>
                <?php if ($item['image_path']): ?>
                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="img-preview" alt="<?= trans('admin_news_current_image') ?>">
                    <label style="margin-top:.5rem;display:flex;align-items:center;gap:.4rem;font-size:.8rem">
                        <input type="checkbox" name="remove_image" value="1"> <?= trans('admin_news_remove_image') ?>
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
                            <label><?= trans('admin_news_title_nl_required') ?></label>
                            <input type="text" name="title_nl" value="<?= htmlspecialchars($item['title_nl'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= trans('admin_news_content_nl_required') ?></label>
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
                <button type="submit" name="publish_action" value="publish_now" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?= trans('admin_news_publish_now') ?></button>
                <button type="submit" name="publish_action" value="save_draft" class="btn btn-ghost"><i class="fas fa-floppy-disk"></i> <?= trans('admin_news_save_draft') ?></button>
                <a href="?page=admin&section=news" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
            <?php if (!empty($allTags)): ?>
            <div class="form-group" style="margin-top:1rem">
                <label><?= trans('admin_tags') ?></label>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;padding:.5rem;background:var(--bg);border:1px solid var(--border);border-radius:6px">
                    <?php foreach ($allTags as $tag): ?>
                    <label style="display:flex;align-items:center;gap:.3rem;cursor:pointer;font-size:.85rem">
                        <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                               <?= in_array($tag['id'], $currentTagIds ?? [], true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>
