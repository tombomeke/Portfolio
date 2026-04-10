<section class="news-page">
    <div class="container">
        <h1>News</h1>

        <?php if (empty($items)): ?>
            <p class="no-content">Nog geen nieuwsberichten gepubliceerd.</p>
        <?php else: ?>
        <div class="news-grid">
            <?php foreach ($items as $item): ?>
            <article class="news-card">
                <?php if ($item['image_path']): ?>
                <div class="news-card-image">
                    <img src="<?= htmlspecialchars($item['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="lazy">
                </div>
                <?php endif; ?>
                <div class="news-card-body">
                    <time class="news-date" datetime="<?= htmlspecialchars($item['published_at'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= date('d M Y', strtotime($item['published_at'])) ?>
                    </time>
                    <h2 class="news-card-title">
                        <a href="?page=news-item&id=<?= (int) $item['id'] ?>">
                            <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h2>
                    <p class="news-card-excerpt">
                        <?= htmlspecialchars(mb_substr(strip_tags($item['content']), 0, 160), ENT_QUOTES, 'UTF-8') ?>…
                    </p>
                    <a href="?page=news-item&id=<?= (int) $item['id'] ?>" class="btn btn-outline btn-sm">
                        Lees meer →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=news&p=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Vorige</a>
            <?php endif; ?>
            <span><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=news&p=<?= $page + 1 ?>" class="btn btn-outline">Volgende &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.news-page { padding: 4rem 0; }
.news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem; }
.news-card { border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; }
.news-card-image img { width: 100%; height: 180px; object-fit: cover; }
.news-card-body { padding: 1.25rem; display: flex; flex-direction: column; gap: .5rem; flex: 1; }
.news-date { font-size: .8rem; color: var(--text-muted, #6b7280); }
.news-card-title { font-size: 1.1rem; margin: 0; }
.news-card-title a { text-decoration: none; color: inherit; }
.news-card-title a:hover { text-decoration: underline; }
.news-card-excerpt { font-size: .9rem; color: var(--text-muted, #6b7280); flex: 1; }
.btn-sm { font-size: .85rem; padding: .35rem .75rem; }
.pagination { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 2rem; }
.no-content { color: var(--text-muted, #6b7280); margin-top: 2rem; }
</style>
