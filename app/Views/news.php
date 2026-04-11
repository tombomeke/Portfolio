<section class="news-page">
    <div class="container">
        <div class="page-header-content" style="margin-bottom:2rem">
            <h1>News</h1>
            <?php if (!empty($activeTag)): ?>
            <div class="active-filter" style="display:flex;align-items:center;gap:.6rem;margin-top:.6rem">
                <span style="font-size:.85rem;color:var(--text-muted)">Gefilterd op:</span>
                <span class="tag-chip"><i class="fas fa-tag"></i> <?= htmlspecialchars($activeTag) ?></span>
                <a href="?page=news" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Wis filter</a>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <p class="no-content">Nog geen nieuwsberichten gepubliceerd.</p>
        <?php else: ?>
        <div class="news-grid">
            <?php foreach ($items as $item): ?>
            <article class="news-card">
                <?php if ($item['image_path']): ?>
                <div class="news-card-image">
                    <img src="<?= htmlspecialchars($item['image_path']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                </div>
                <?php endif; ?>
                <div class="news-card-body">
                    <time class="news-date" datetime="<?= htmlspecialchars($item['published_at']) ?>">
                        <?= date('d M Y', strtotime($item['published_at'])) ?>
                    </time>
                    <?php if (!empty($item['tags'])): ?>
                    <div class="news-card-tags" style="margin:.4rem 0;display:flex;flex-wrap:wrap;gap:.3rem">
                        <?php foreach ($item['tags'] as $tag): ?>
                        <a href="?page=news&tag=<?= urlencode($tag['slug']) ?>" class="tag-chip tag-chip--small">
                            #<?= htmlspecialchars($tag['slug']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <h2 class="news-card-title">
                        <a href="?page=news-item&id=<?= (int) $item['id'] ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </h2>
                    <p class="news-card-excerpt">
                        <?= htmlspecialchars(mb_substr(strip_tags($item['content']), 0, 160)) ?>…
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
            <?php $tagQs = !empty($activeTag) ? '&tag=' . urlencode($activeTag) : ''; ?>
            <?php if ($page > 1): ?>
                <a href="?page=news&p=<?= $page - 1 ?><?= $tagQs ?>" class="btn btn-outline">&laquo; Vorige</a>
            <?php endif; ?>
            <span><?= $page ?> / <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=news&p=<?= $page + 1 ?><?= $tagQs ?>" class="btn btn-outline">Volgende &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.tag-chip { display:inline-block; background:rgba(59,130,246,.12); color:var(--primary-color,#3b82f6); border-radius:4px; padding:.2rem .5rem; font-size:.78rem; font-weight:500; text-decoration:none; transition:background .15s; }
.tag-chip:hover { background:rgba(59,130,246,.22); }
.tag-chip--small { font-size:.7rem; padding:.1rem .4rem; }
.active-filter .tag-chip { background:rgba(59,130,246,.2); }
</style>
