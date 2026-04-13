<section class="projects">
    <div class="container">
        <h1><i class="fas fa-list-check"></i> <?= trans('project_roadmaps_title') ?></h1>
        <p class="section-intro"><?= trans('project_roadmaps_intro') ?></p>

        <?php if (empty($projects)): ?>
            <p class="section-intro"><?= trans('project_roadmaps_none_found') ?></p>
        <?php else: ?>

            <div class="roadmap-page-toolbar">
                <input type="text" id="roadmap-search" placeholder="<?= htmlspecialchars(trans('project_roadmaps_search_placeholder')) ?>"
                       class="roadmap-search-input">
            </div>

            <div class="projects-grid" id="roadmap-grid">
                <?php foreach ($projects as $project): ?>
                    <?php
                    $pid        = (int) $project['id'];
                    $summary    = $syncSummary[$pid] ?? [];
                    $openCount  = (int) ($summary['openCount']  ?? 0);
                    $doneCount  = (int) ($summary['doneCount']  ?? 0);
                    $totalCount = (int) ($summary['totalCount'] ?? 0);
                    $lastSyncAt = (string) ($summary['lastSyncAt'] ?? '');
                    $progress   = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
                    ?>
                    <article class="project-card roadmap-project-card" data-roadmap-title="<?= htmlspecialchars(strtolower($project['title'])) ?>">
                        <div class="project-content">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <p class="roadmap-project-description"><?= htmlspecialchars($project['description']) ?></p>

                            <?php if ($totalCount > 0): ?>
                                <div class="roadmap-progress" title="<?= $doneCount ?>/<?= $totalCount ?> <?= trans('project_roadmaps_done_percent') ?>">
                                    <div class="roadmap-progress-bar" style="width:<?= $progress ?>%"></div>
                                </div>
                                <p class="roadmap-card-stats">
                                    <span class="roadmap-stat-pill roadmap-stat-pill--primary"><?= $progress ?>% <?= trans('project_roadmaps_done_percent') ?></span>
                                    <span class="roadmap-stat-pill roadmap-stat-pill--warning"><?= $openCount ?> <?= trans('roadmap_open_label') ?></span>
                                    <span class="roadmap-stat-pill roadmap-stat-pill--success"><?= $doneCount ?> <?= trans('project_roadmaps_done_count') ?></span>
                                </p>
                            <?php else: ?>
                                <p class="roadmap-card-empty">
                                    <?= trans('project_roadmaps_not_synced') ?>
                                </p>
                            <?php endif; ?>

                            <p class="roadmap-card-sync">
                                <strong><?= trans('project_roadmaps_synced') ?></strong>
                                <?= $lastSyncAt ? htmlspecialchars(date('d/m/Y H:i', strtotime($lastSyncAt))) : trans('project_roadmaps_not_yet') ?>
                            </p>

                            <div class="project-links roadmap-project-links">
                                <a class="btn btn-primary btn-sm" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap">
                                    <?= trans('project_roadmaps_view') ?>
                                </a>
                                <?php if (!empty($project['repo_url'])): ?>
                                    <a class="btn btn-ghost btn-sm" href="<?= htmlspecialchars((string) $project['repo_url']) ?>" target="_blank" rel="noopener">
                                        <i class="fab fa-github"></i> GitHub
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <p id="roadmap-no-results" style="display:none;color:var(--text-muted)"><?= trans('project_roadmaps_no_results') ?></p>
        <?php endif; ?>
    </div>
</section>

<style>
.roadmap-page-toolbar {
    margin-bottom: 1.25rem;
    display: flex;
    justify-content: flex-start;
}

.roadmap-search-input {
    max-width: 360px;
    width: 100%;
    padding: .7rem .95rem;
    border: 1px solid rgba(148,163,184,.26);
    border-radius: 14px;
    background: rgba(15,23,42,.45);
    color: #e2e8f0;
    font-size: .92rem;
    box-shadow: 0 8px 20px rgba(15,23,42,.18);
}

.roadmap-search-input::placeholder {
    color: #94a3b8;
}

.roadmap-project-card {
    border: 1px solid rgba(99,102,241,.18);
    box-shadow: 0 10px 24px rgba(15,23,42,.22);
    background: linear-gradient(180deg, rgba(59,130,246,.10), rgba(15,23,42,.55));
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    max-width: 460px;
}

.roadmap-project-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99,102,241,.35);
    box-shadow: 0 16px 36px rgba(15,23,42,.30);
}

.roadmap-project-card .project-content {
    gap: .7rem;
}

.roadmap-project-card h3 {
    color: #f8fafc;
}

.roadmap-project-description {
    color: #94a3b8;
    font-size: .88rem;
    margin: 0;
    line-height: 1.5;
}

.roadmap-progress {
    height: 8px;
    border-radius: 999px;
    background: rgba(148,163,184,.18);
    overflow: hidden;
    margin-bottom: .45rem;
}
.roadmap-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #7c3aed);
    border-radius: 999px;
    transition: width .3s;
}

.roadmap-card-stats {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin: .1rem 0 .65rem;
    font-size: .76rem;
}

.roadmap-card-sync,
.roadmap-card-empty {
    font-size: .8rem;
    color: #94a3b8;
    margin: 0;
}

.roadmap-stat-pill {
    display: inline-flex;
    align-items: center;
    padding: .26rem .55rem;
    border-radius: 999px;
    font-weight: 700;
    border: 1px solid transparent;
}
.roadmap-stat-pill--primary {
    background: rgba(99,102,241,.22);
    color: #c7d2fe;
    border-color: rgba(99,102,241,.35);
}
.roadmap-stat-pill--warning {
    background: rgba(245,158,11,.2);
    color: #fcd34d;
    border-color: rgba(245,158,11,.35);
}
.roadmap-stat-pill--success {
    background: rgba(34,197,94,.2);
    color: #86efac;
    border-color: rgba(34,197,94,.35);
}

.roadmap-project-links {
    margin-top: .75rem;
    flex-wrap: wrap;
}

.roadmap-project-links .btn {
    min-width: 126px;
    justify-content: center;
}

@media (max-width: 768px) {
    .roadmap-page-toolbar {
        margin-bottom: 1rem;
    }

    .roadmap-search-input {
        max-width: 100%;
    }

    .roadmap-card-stats {
        gap: .25rem;
    }

    .roadmap-project-card {
        max-width: 100%;
    }
}
</style>

<script>
(function () {
    const input   = document.getElementById('roadmap-search');
    const grid    = document.getElementById('roadmap-grid');
    const noRes   = document.getElementById('roadmap-no-results');
    if (!input || !grid) return;

    input.addEventListener('input', function () {
        const q     = this.value.toLowerCase().trim();
        const cards = grid.querySelectorAll('[data-roadmap-title]');
        let   shown = 0;

        cards.forEach(card => {
            const match = q === '' || card.dataset.roadmapTitle.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) shown++;
        });

        noRes.style.display = shown === 0 ? '' : 'none';
    });
})();
</script>
