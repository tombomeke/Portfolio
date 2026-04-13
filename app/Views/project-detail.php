<?php
$projectImages = (array) ($project['images'] ?? []);
$activeTab = ($tab ?? 'overview') === 'roadmap' ? 'roadmap' : 'overview';
$roadmapItems = (array) (($projectRoadmap['items'] ?? []));
?>

<section class="projects project-detail-page">
    <div class="container">
        <p class="section-intro"><a href="?page=projects">&larr; <?= trans('project_back_to_projects') ?></a></p>

        <h1><i class="fas fa-folder-open"></i> <?= htmlspecialchars((string) ($project['title'] ?? 'Project')) ?></h1>
        <p class="section-intro"><?= htmlspecialchars((string) ($project['description'] ?? '')) ?></p>

        <?php if (!empty($projectImages)): ?>
            <div class="project-gallery" data-gallery>
                <?php if (count($projectImages) > 1): ?>
                    <button type="button" class="gallery-nav" data-gallery-prev aria-label="<?= trans('project_previous_image') ?>">&larr;</button>
                <?php else: ?>
                    <span class="gallery-nav gallery-nav--hidden" aria-hidden="true"></span>
                <?php endif; ?>
                <div class="gallery-image-wrap">
                    <img src="<?= htmlspecialchars((string) $projectImages[0]) ?>" alt="Project afbeelding" class="project-detail-image" data-gallery-image>
                    <?php if (count($projectImages) > 1): ?>
                        <div class="gallery-dots" data-gallery-dots>
                            <?php foreach ($projectImages as $idx => $_): ?>
                                <button type="button" class="gallery-dot <?= $idx === 0 ? 'gallery-dot--active' : '' ?>"
                                        data-gallery-dot="<?= $idx ?>" aria-label="<?= trans('project_image') ?> <?= $idx + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($projectImages) > 1): ?>
                    <button type="button" class="gallery-nav" data-gallery-next aria-label="<?= trans('project_next_image') ?>">&rarr;</button>
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
            <a href="?page=project-roadmaps" class="btn btn-ghost"><?= trans('project_all_roadmaps') ?></a>
            <?php if (!empty($canSyncRoadmap) && !empty($project['repo_url'])): ?>
                <a href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap&amp;sync=1" class="btn btn-secondary"><?= trans('project_sync_roadmap') ?></a>
            <?php endif; ?>
        </div>

        <?php if (!empty($syncMessage)): ?>
            <div class="alert alert-info" style="margin-bottom:1rem"><?= htmlspecialchars((string) $syncMessage) ?></div>
        <?php endif; ?>

        <div class="project-detail-tabs" role="tablist">
            <a class="btn <?= $activeTab === 'overview' ? 'btn-primary' : 'btn-ghost' ?>" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=overview"><?= trans('project_tab_overview') ?></a>
            <a class="btn <?= $activeTab === 'roadmap' ? 'btn-primary' : 'btn-ghost' ?>" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap"><?= trans('project_tab_roadmap') ?></a>
        </div>

        <?php if ($activeTab === 'overview'): ?>
            <article class="project-card" style="margin-top:1rem">
                <div class="project-content">
                    <h3><?= trans('project_description_title') ?></h3>
                    <p><?= nl2br(htmlspecialchars((string) ($project['long_description'] ?: $project['description']))) ?></p>
                    <?php if (!empty($project['features'])): ?>
                        <h3 style="margin-top:1rem"><?= trans('project_features_title') ?></h3>
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
            <article class="project-card roadmap-panel" style="margin-top:1rem">
                <div class="project-content">
                    <div class="roadmap-header">
                        <div>
                            <h3 style="margin:0"><?= trans('roadmap_todos_title') ?></h3>
                            <p class="roadmap-header-meta">
                                <?= $openCount ?> <?= trans('roadmap_open_label') ?> · <?= $totalCount ?> <?= trans('roadmap_total_label') ?>
                                <?php if ($lastSyncAt): ?>
                                    · <?= trans('roadmap_synced_on') ?> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($lastSyncAt))) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="roadmap-summary-badges">
                            <span class="roadmap-summary-badge roadmap-summary-badge--open"><?= $openCount ?> <?= trans('roadmap_open_label') ?></span>
                            <span class="roadmap-summary-badge roadmap-summary-badge--total"><?= $totalCount ?> <?= trans('roadmap_items_label') ?></span>
                        </div>
                    </div>

                    <div class="roadmap-filters">
                        <a href="<?= $filterBase ?>" class="btn btn-sm <?= $activeFilter === '' ? 'btn-primary' : 'btn-ghost' ?>"><?= trans('roadmap_filter_all') ?></a>
                        <a href="<?= $filterBase ?>&amp;filter=open" class="btn btn-sm <?= $activeFilter === 'open' ? 'btn-primary' : 'btn-ghost' ?>"><?= trans('roadmap_filter_open') ?></a>
                        <a href="<?= $filterBase ?>&amp;filter=done" class="btn btn-sm <?= $activeFilter === 'done' ? 'btn-primary' : 'btn-ghost' ?>"><?= trans('roadmap_filter_done') ?></a>
                        <a href="<?= $filterBase ?>&amp;filter=high" class="btn btn-sm <?= $activeFilter === 'high' ? 'btn-primary' : 'btn-ghost' ?>"><?= trans('roadmap_filter_high') ?></a>
                    </div>

                    <?php if (empty($roadmapItems)): ?>
                        <div class="roadmap-empty">
                            <i class="fas fa-list-check" style="font-size:2rem;opacity:.25;margin-bottom:.5rem"></i>
                            <?php if ($activeFilter !== ''): ?>
                                <p><?= trans('roadmap_no_items_for_filter') ?></p>
                                <a href="<?= $filterBase ?>" class="btn btn-ghost btn-sm"><?= trans('roadmap_show_all') ?></a>
                            <?php else: ?>
                                <p><?= trans('roadmap_not_synced_for_project') ?></p>
                                <?php if (!empty($canSyncRoadmap) && $repoUrl !== ''): ?>
                                    <a href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap&amp;sync=1"
                                       class="btn btn-ghost btn-sm"><?= trans('roadmap_sync_now') ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php
                        // Group items by file for better readability
                        $grouped = [];
                        foreach ($roadmapItems as $item) {
                            $key = (string) ($item['file'] ?? '');
                            $grouped[$key][] = $item;
                        }
                        ?>
                        <div class="roadmap-todo-list">
                        <?php foreach ($grouped as $fileKey => $fileItems): ?>
                            <?php if ($fileKey !== '' && count($grouped) > 1): ?>
                                <div class="roadmap-file-group">
                                    <?php
                                    // TODO(ux): [P3] '/blob/main/' is hardcoded — repos may use 'master' or
                                    // another default branch. Store default_branch on the project record.
                                    $fileGithubLink = '';
                                    if ($repoUrl !== '') {
                                        $fileGithubLink = rtrim($repoUrl, '/') . '/blob/main/' . ltrim($fileKey, '/');
                                    }
                                    ?>
                                    <?php if ($fileGithubLink !== ''): ?>
                                        <a href="<?= htmlspecialchars($fileGithubLink) ?>" target="_blank" rel="noopener"
                                           class="roadmap-file-label"><?= htmlspecialchars($fileKey) ?></a>
                                    <?php else: ?>
                                        <span class="roadmap-file-label"><?= htmlspecialchars($fileKey) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($fileItems as $item): ?>
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
                                <div class="roadmap-todo-item <?= $isDone ? 'roadmap-todo-item--done' : '' ?> <?= $isHigh ? 'roadmap-todo-item--high' : '' ?>">
                                    <span class="roadmap-todo-status"><?= $isDone ? '✓' : '○' ?></span>
                                    <span class="roadmap-todo-body">
                                        <span class="roadmap-todo-text"><?= htmlspecialchars((string) ($item['text'] ?? '')) ?></span>
                                        <span class="roadmap-todo-meta">
                                            <?php if ($file !== '' && $line > 0): ?>
                                                <?php if ($githubLink !== ''): ?>
                                                    <a href="<?= htmlspecialchars($githubLink) ?>" target="_blank" rel="noopener"
                                                       title="<?= trans('project_open_github') ?>">:<?= $line ?></a>
                                                <?php else: ?>
                                                    <span>:<?= $line ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($isHigh && !$isDone): ?>
                                                <span class="roadmap-badge-high"><i class="fas fa-arrow-up"></i> high</span>
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </div>
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
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = images[index];
            img.style.opacity = '1';
        }, 120);
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
    transition: opacity .12s ease;
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

.roadmap-panel {
    border: 1px solid rgba(99,102,241,.12);
    box-shadow: 0 10px 30px rgba(15,23,42,.06);
    background: linear-gradient(180deg, rgba(99,102,241,.04), rgba(255,255,255,0));
}

.roadmap-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: .9rem;
}

.roadmap-header-meta {
    margin: .2rem 0 0;
    color: var(--text-muted, #94a3b8);
    font-size: .8rem;
}

.roadmap-summary-badges {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}

.roadmap-summary-badge {
    display: inline-flex;
    align-items: center;
    padding: .38rem .6rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .01em;
    border: 1px solid transparent;
}
.roadmap-summary-badge--open {
    background: rgba(245,158,11,.12);
    color: #b45309;
    border-color: rgba(245,158,11,.2);
}
.roadmap-summary-badge--total {
    background: rgba(99,102,241,.08);
    color: #4f46e5;
    border-color: rgba(99,102,241,.18);
}

.roadmap-filters {
    display: flex;
    gap: .45rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    padding: .5rem;
    border-radius: 12px;
    background: rgba(148,163,184,.08);
    border: 1px solid rgba(148,163,184,.14);
}

/* Roadmap empty state */
.roadmap-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: 2.5rem 1rem;
    text-align: center;
    color: var(--text-muted, #94a3b8);
    border: 1px dashed rgba(148,163,184,.35);
    border-radius: 14px;
    background: rgba(255,255,255,.55);
}
.roadmap-empty p { margin: 0; }

/* Roadmap TODO list */
.roadmap-todo-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.roadmap-file-group {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .35rem 0 .2rem;
    margin-top: .6rem;
}
.roadmap-file-group:first-child { margin-top: 0; }
.roadmap-file-label {
    font-family: 'Courier New', monospace;
    font-size: .72rem;
    color: #334155;
    background: rgba(99,102,241,.10);
    border: 1px solid rgba(99,102,241,.14);
    border-radius: 999px;
    padding: 3px 8px;
    text-decoration: none;
    transition: color .1s, background .1s;
}
a.roadmap-file-label:hover {
    color: var(--primary, #4f46e5);
    background: rgba(99,102,241,.16);
}
.roadmap-todo-item {
    display: flex;
    gap: .65rem;
    align-items: flex-start;
    padding: .7rem .8rem;
    border-radius: 12px;
    border: 1px solid rgba(148,163,184,.18);
    background: rgba(255,255,255,.78);
    transition: background .12s, border-color .12s, transform .12s, box-shadow .12s;
}
.roadmap-todo-item:hover {
    background: rgba(255,255,255,.96);
    border-color: rgba(99,102,241,.22);
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(15,23,42,.04);
}
.roadmap-todo-item--done {
    opacity: .52;
    background: rgba(34,197,94,.05);
    border-color: rgba(34,197,94,.18);
}
.roadmap-todo-item--done:hover {
    background: rgba(34,197,94,.07);
}
.roadmap-todo-item--high:not(.roadmap-todo-item--done) {
    border-color: rgba(245,158,11,.25);
    background: rgba(245,158,11,.05);
}
.roadmap-todo-item--high:not(.roadmap-todo-item--done):hover {
    background: rgba(245,158,11,.08);
}
.roadmap-todo-status {
    font-size: .82rem;
    color: var(--text-muted, #94a3b8);
    flex-shrink: 0;
    margin-top: .12rem;
    line-height: 1;
    width: 1em;
    text-align: center;
}
.roadmap-todo-item--done .roadmap-todo-status { color: #22c55e; }
.roadmap-todo-item--high:not(.roadmap-todo-item--done) .roadmap-todo-status { color: #f59e0b; }
.roadmap-todo-body {
    display: flex;
    flex-direction: column;
    gap: .1rem;
    min-width: 0;
    flex: 1;
}
.roadmap-todo-text {
    font-size: .875rem;
    line-height: 1.45;
    color: var(--text-primary);
    word-break: break-word;
}
.roadmap-todo-item--done .roadmap-todo-text {
    text-decoration: line-through;
    color: var(--text-muted, #94a3b8);
}
.roadmap-todo-meta {
    font-size: .72rem;
    color: var(--text-muted, #94a3b8);
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .3rem;
    margin-top: .05rem;
}
.roadmap-todo-meta a {
    color: var(--primary-color, #6366f1);
    text-decoration: none;
    font-family: 'Courier New', monospace;
}
.roadmap-todo-meta a:hover { text-decoration: underline; }
.roadmap-badge-high {
    display: inline-flex;
    align-items: center;
    gap: .2em;
    background: rgba(245,158,11,.14);
    color: #d97706;
    border-radius: 999px;
    padding: 2px 7px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .02em;
    text-transform: uppercase;
}

@media (max-width: 768px) {
    .project-detail-page .project-gallery {
        grid-template-columns: 44px 1fr 44px;
    }

    .roadmap-header {
        align-items: stretch;
    }

    .roadmap-summary-badges {
        width: 100%;
    }

    .roadmap-summary-badge {
        flex: 1 1 auto;
        justify-content: center;
    }
}
</style>
