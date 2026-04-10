<section class="news-item-page">
    <div class="container container--narrow">

        <a href="?page=news" class="back-link">&larr; Terug naar News</a>

        <article>
            <header class="news-item-header">
                <?php if ($item['image_path']): ?>
                <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>"
                     class="news-item-image">
                <?php endif; ?>
                <h1><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <time class="news-date" datetime="<?= htmlspecialchars($item['published_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= date('d F Y', strtotime($item['published_at'])) ?>
                </time>
            </header>

            <div class="news-item-content">
                <?= nl2br(htmlspecialchars($item['content'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </article>

    </div>
</section>

<style>
.news-item-page { padding: 4rem 0; }
.container--narrow { max-width: 760px; }
.back-link { display: inline-block; margin-bottom: 2rem; color: var(--primary, #3b82f6); text-decoration: none; }
.back-link:hover { text-decoration: underline; }
.news-item-header { margin-bottom: 2rem; }
.news-item-image { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem; }
.news-item-header h1 { margin-bottom: .4rem; }
.news-date { font-size: .85rem; color: var(--text-muted, #6b7280); }
.news-item-content { line-height: 1.8; font-size: 1.05rem; }
</style>
