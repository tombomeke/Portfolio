<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_news_add_post') ?></span>
        <a href="?page=admin&section=news" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>

    <form method="POST" action="?page=admin&section=news&action=create" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>

        <div class="form-grid" style="gap:1.5rem">

            <div class="form-group">
                <label><?= trans('admin_news_publish_date_hint') ?></label>
                <input type="datetime-local" name="published_at">
            </div>

            <div class="form-group">
                <label><?= trans('admin_news_image_optional') ?></label>
                <input type="file" name="image" accept="image/*">
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
                            <input type="text" name="title_nl" required>
                        </div>
                        <div class="form-group">
                            <label><?= trans('admin_news_content_nl_required') ?></label>
                            <textarea name="content_nl" class="tall" required placeholder="<?= trans('admin_news_content_nl_placeholder') ?>"></textarea>
                        </div>
                    </div>
                </div>

                <div id="lang-en" class="lang-panel">
                    <div class="form-grid" style="gap:1rem">
                        <div class="form-group">
                            <label>Title (EN) *</label>
                            <input type="text" name="title_en" required>
                        </div>
                        <div class="form-group">
                            <label>Content (EN) *</label>
                            <textarea name="content_en" class="tall" required placeholder="Write the article in English..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($allTags)): ?>
            <div class="form-group">
                <label><?= trans('admin_tags') ?></label>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem;padding:.5rem;background:var(--bg);border:1px solid var(--border);border-radius:6px">
                    <?php foreach ($allTags as $tag): ?>
                    <label style="display:flex;align-items:center;gap:.3rem;cursor:pointer;font-size:.85rem">
                        <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>">
                        <?= htmlspecialchars($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_save') ?></button>
                <a href="?page=admin&section=news" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
        </div>
    </form>
</div>
