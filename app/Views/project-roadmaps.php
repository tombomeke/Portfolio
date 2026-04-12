<section class="projects">
    <div class="container">
        <h1><i class="fas fa-list-check"></i> Project Roadmaps</h1>
        <p class="section-intro">Overzicht van TODO-items per project, gesynchroniseerd via ReadmeSync.API.</p>

        <?php if (empty($projects)): ?>
            <p class="section-intro">Nog geen projecten gevonden.</p>
        <?php else: ?>

            <div style="margin-bottom:1.25rem">
                <input type="text" id="roadmap-search" placeholder="Zoek project…"
                       style="max-width:320px;width:100%;padding:.5rem .8rem;border:1px solid var(--border-color);border-radius:8px;background:var(--surface-color);color:var(--text-primary);font-size:.9rem">
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
                    <article class="project-card" data-roadmap-title="<?= htmlspecialchars(strtolower($project['title'])) ?>">
                        <div class="project-content">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <p style="color:var(--text-muted);font-size:.85rem"><?= htmlspecialchars($project['description']) ?></p>

                            <?php if ($totalCount > 0): ?>
                                <div class="roadmap-progress" title="<?= $doneCount ?>/<?= $totalCount ?> klaar">
                                    <div class="roadmap-progress-bar" style="width:<?= $progress ?>%"></div>
                                </div>
                                <p style="font-size:.8rem;color:var(--text-muted);margin:.3rem 0 .6rem">
                                    <?= $progress ?>% klaar &middot; <?= $openCount ?> open &middot; <?= $doneCount ?> gedaan
                                </p>
                            <?php else: ?>
                                <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:.6rem">
                                    Nog niet gesynchroniseerd.
                                </p>
                            <?php endif; ?>

                            <p style="font-size:.8rem;color:var(--text-muted)">
                                <strong>Gesynchroniseerd:</strong>
                                <?= $lastSyncAt ? htmlspecialchars(date('d/m/Y H:i', strtotime($lastSyncAt))) : 'Nog niet' ?>
                            </p>

                            <div class="project-links" style="margin-top:.75rem">
                                <a class="btn btn-primary btn-sm" href="?page=project&amp;slug=<?= urlencode((string) $project['slug']) ?>&amp;tab=roadmap">
                                    Bekijk roadmap
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

            <p id="roadmap-no-results" style="display:none;color:var(--text-muted)">Geen projecten gevonden.</p>
        <?php endif; ?>
    </div>
</section>

<style>
.roadmap-progress {
    height: 7px;
    border-radius: 4px;
    background: var(--border-color);
    overflow: hidden;
    margin-bottom: .25rem;
}
.roadmap-progress-bar {
    height: 100%;
    background: var(--primary, #4f46e5);
    border-radius: 4px;
    transition: width .3s;
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
