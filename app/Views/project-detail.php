<?php
$projectImages = (array) ($project['images'] ?? []);
$activeTab = ($tab ?? 'overview') === 'roadmap' ? 'roadmap' : 'overview';
$roadmapItems = (array) (($projectRoadmap['items'] ?? []));
?>

<section class="projects project-detail-page">
    <div class="container">
        <p class="section-intro"><a href="?page=projects">&larr; Terug naar projecten</a></p>

        <h1><i class="fas fa-folder-open"></i> <?= htmlspecialchars((string) ($project['title'] ?? 'Project')) ?></h1>
        <p class="section-intro"><?= htmlspecialchars((string) ($project['description'] ?? '')) ?></p>

        <?php if (!empty($projectImages)): ?>
            <div class="project-gallery" data-gallery>
                <?php if (count($projectImages) > 1): ?>
                    <button type="button" class="gallery-nav" data-gallery-prev aria-label="Vorige afbeelding">&larr;</button>
                <?php else: ?>
                    <span class="gallery-nav gallery-nav--hidden" aria-hidden="true"></span>
                <?php endif; ?>
                <div class="gallery-image-wrap">
                    <img src="<?= htmlspecialchars((string) $projectImages[0]) ?>" alt="Project afbeelding" class="project-detail-image" data-gallery-image>
                    <?php if (count($projectImages) > 1): ?>
                        <div class="gallery-dots" data-gallery-dots>
                            <?php foreach ($projectImages as $idx => $_): ?>
                                <button type="button" class="gallery-dot <?= $idx === 0 ? 'gallery-dot--active' : '' ?>"
                                        data-gallery-dot="<?= $idx ?>" aria-label="Afbeelding <?= $idx + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($projectImages) > 1): ?>
                    <button type="button" class="gallery-nav" data-gallery-next aria-label="Volgende afbeelding">&rarr;</button>
                <?php else: ?>
                    <span class="gallery-nav gallery-nav--hidden" aria-hidden="true"></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="project-links" style="margin-bottom:1rem">
            <?php if (!empty($project['repo_url'])): ?>
                <a href="<?= htmlspecialchars((string) $project['repo_url']) ?>" target="_blank" class="btn btn-secondary"><i class="fab fa-github"></i> GitHub</a>
            <?php endif; ?>
            <?php if (!empty($project['demo_url'])): ?>
                <a href="<?= htmlspecialchars((string) $project['demo_url']) ?>" target="_blank" class="btn btn-primary"><i class="fas fa-link"></i> Demo</a>
            <?php endif; ?>
            <a href="?page=project-roadmaps" class="btn btn-ghost">Alle roadmaps</a>
            <?php if (!empty($canSyncRoadmap) && !empty($project['repo_url'])): ?>
                <a href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap&amp;sync=1" class="btn btn-secondary">Sync roadmap via API</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($syncMessage)): ?>
            <div class="alert alert-info" style="margin-bottom:1rem"><?= htmlspecialchars((string) $syncMessage) ?></div>
        <?php endif; ?>

        <div class="project-detail-tabs" role="tablist">
            <a class="btn <?= $activeTab === 'overview' ? 'btn-primary' : 'btn-ghost' ?>" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=overview">Overzicht</a>
            <a class="btn <?= $activeTab === 'roadmap' ? 'btn-primary' : 'btn-ghost' ?>" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap">Roadmap</a>
        </div>

        <?php if ($activeTab === 'overview'): ?>
            <article class="project-card" style="margin-top:1rem">
                <div class="project-content">
                    <h3>Beschrijving</h3>
                    <p><?= nl2br(htmlspecialchars((string) ($project['long_description'] ?: $project['description']))) ?></p>
                    <?php if (!empty($project['features'])): ?>
                        <h3 style="margin-top:1rem">Features</h3>
                        <ul>
                            <?php foreach ((array) $project['features'] as $feature): ?>
                                <li><?= htmlspecialchars((string) $feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </article>
        <?php else: ?>
            <?php
            $activeFilter   = (string) ($roadmapFilter ?? '');
            $slugEncoded    = urlencode((string) $project['slug']);
            $repoUrl        = (string) ($project['repo_url'] ?? '');
            $lastSyncAt     = (string) ($projectRoadmap['lastSyncAt'] ?? '');
            $openCount      = (int) ($projectRoadmap['openCount']     ?? count(array_filter($roadmapItems, fn($i) => ($i['status'] ?? '') !== 'done')));
            $totalCount     = (int) ($projectRoadmap['totalCount']    ?? count($roadmapItems));
            $filterBase     = "?page=project&amp;slug={$slugEncoded}&amp;tab=roadmap";
            ?>
            <article class="project-card" style="margin-top:1rem">
                <div class="project-content">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.75rem">
                        <h3 style="margin:0">Roadmap TODOs</h3>
                        <small style="color:var(--text-muted)">
                            <?= $openCount ?> open · <?= $totalCount ?> totaal
                            <?php if ($lastSyncAt): ?>
                                · gesynchroniseerd op <?= htmlspecialchars(date('d/m/Y H:i', strtotime($lastSyncAt))) ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem">
                        <a href="<?= $filterBase ?>" class="btn btn-sm <?= $activeFilter === '' ? 'btn-primary' : 'btn-ghost' ?>">Alle</a>
                        <a href="<?= $filterBase ?>&amp;filter=open" class="btn btn-sm <?= $activeFilter === 'open' ? 'btn-primary' : 'btn-ghost' ?>">Open</a>
                        <a href="<?= $filterBase ?>&amp;filter=done" class="btn btn-sm <?= $activeFilter === 'done' ? 'btn-primary' : 'btn-ghost' ?>">Klaar</a>
                        <a href="<?= $filterBase ?>&amp;filter=high" class="btn btn-sm <?= $activeFilter === 'high' ? 'btn-primary' : 'btn-ghost' ?>">Hoge prioriteit</a>
                    </div>

                    <?php if (empty($roadmapItems)): ?>
                        <p style="color:var(--text-muted)">
                            <?php if ($activeFilter !== ''): ?>
                                Geen items gevonden voor dit filter. <a href="<?= $filterBase ?>">Alle tonen</a>
                            <?php else: ?>
                                Nog geen roadmap items gesynchroniseerd voor dit project.
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <ul class="roadmap-todo-list">
                            <?php foreach ($roadmapItems as $item): ?>
                                <?php
                                $isDone     = ($item['status'] ?? '') === 'done';
                                $isHigh     = ($item['priority'] ?? '') === 'high';
                                $file       = (string) ($item['file'] ?? '');
                                $line       = (int) ($item['line'] ?? 0);
                                $githubLink = '';
                                if ($repoUrl !== '' && $file !== '') {
                                    $githubLink = rtrim($repoUrl, '/') . '/blob/main/' . ltrim($file, '/') . ($line > 0 ? "#L{$line}" : '');
                                }
                                ?>
                                <li class="roadmap-todo-item <?= $isDone ? 'roadmap-todo-item--done' : '' ?> <?= $isHigh ? 'roadmap-todo-item--high' : '' ?>">
                                    <span class="roadmap-todo-status"><?= $isDone ? '✓' : '○' ?></span>
                                    <span class="roadmap-todo-body">
                                        <span class="roadmap-todo-text"><?= htmlspecialchars((string) ($item['text'] ?? '')) ?></span>
                                        <?php if ($file !== ''): ?>
                                            <span class="roadmap-todo-meta">
                                                <?php if ($githubLink !== ''): ?>
                                                    <a href="<?= htmlspecialchars($githubLink) ?>" target="_blank" rel="noopener"
                                                       title="Bekijk op GitHub"><?= htmlspecialchars($file) ?><?= $line > 0 ? ":{$line}" : '' ?></a>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($file) ?><?= $line > 0 ? ":{$line}" : '' ?>
                                                <?php endif; ?>
                                                <?php if ($isHigh): ?> <span class="roadmap-badge-high">high</span><?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($projectImages) && count($projectImages) > 1): ?>
<script>
(function () {
    const wrap = document.querySelector('[data-gallery]');
    if (!wrap) return;

    const images = <?= json_encode(array_values($projectImages), JSON_UNESCAPED_SLASHES) ?>;
    let index = 0;

    const img  = wrap.querySelector('[data-gallery-image]');
    const prev = wrap.querySelector('[data-gallery-prev]');
    const next = wrap.querySelector('[data-gallery-next]');
    const dots = Array.from(wrap.querySelectorAll('[data-gallery-dot]'));

    const render = () => {
        img.src = images[index];
        dots.forEach((d, i) => d.classList.toggle('gallery-dot--active', i === index));
    };

    if (prev) prev.addEventListener('click', () => { index = (index - 1 + images.length) % images.length; render(); });
    if (next) next.addEventListener('click', () => { index = (index + 1) % images.length; render(); });

    dots.forEach(d => {
        d.addEventListener('click', () => { index = parseInt(d.dataset.galleryDot, 10); render(); });
    });
})();
</script>
<?php endif; ?>

<style>
.project-detail-page .project-gallery {
    display: grid;
    grid-template-columns: 56px 1fr 56px;
    gap: .8rem;
    align-items: center;
    margin-bottom: 1rem;
}

.project-detail-page .gallery-nav {
    height: 56px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background: var(--surface-color);
    color: var(--text-primary);
    cursor: pointer;
    transition: background .15s;
}
.project-detail-page .gallery-nav:hover {
    background: var(--primary, #4f46e5);
    color: #fff;
    border-color: var(--primary, #4f46e5);
}
.project-detail-page .gallery-nav--hidden {
    visibility: hidden;
    pointer-events: none;
}

.gallery-image-wrap {
    position: relative;
}

.project-detail-image {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    border-radius: 14px;
    border: 1px solid var(--border-color);
    display: block;
}

.gallery-dots {
    display: flex;
    justify-content: center;
    gap: .4rem;
    margin-top: .6rem;
}
.gallery-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    border: none;
    background: var(--border-color);
    cursor: pointer;
    padding: 0;
    transition: background .15s, transform .15s;
}
.gallery-dot--active,
.gallery-dot:hover {
    background: var(--primary, #4f46e5);
    transform: scale(1.25);
}

.project-detail-tabs {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}

/* Roadmap TODO list */
.roadmap-todo-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.roadmap-todo-item {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
    padding: .6rem .8rem;
    border-radius: 8px;
    border-left: 3px solid var(--border-color);
    background: var(--surface-color, #fff);
}
.roadmap-todo-item--done {
    opacity: .55;
    border-left-color: #22c55e;
}
.roadmap-todo-item--high {
    border-left-color: #f59e0b;
}
.roadmap-todo-status {
    font-size: .9rem;
    color: var(--text-muted);
    flex-shrink: 0;
    margin-top: .1rem;
}
.roadmap-todo-item--done .roadmap-todo-status {
    color: #22c55e;
}
.roadmap-todo-body {
    display: flex;
    flex-direction: column;
    gap: .15rem;
    min-width: 0;
}
.roadmap-todo-text {
    font-size: .9rem;
    line-height: 1.4;
}
.roadmap-todo-meta {
    font-size: .75rem;
    color: var(--text-muted);
    word-break: break-all;
}
.roadmap-todo-meta a {
    color: var(--primary, #4f46e5);
    text-decoration: none;
}
.roadmap-todo-meta a:hover {
    text-decoration: underline;
}
.roadmap-badge-high {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: .7rem;
    font-weight: 600;
    margin-left: .25rem;
}

@media (max-width: 768px) {
    .project-detail-page .project-gallery {
        grid-template-columns: 44px 1fr 44px;
    }
}
</style>
