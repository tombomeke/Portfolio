<?php
/*
================================================================================
BESTAND 5: /app/Views/projects.php
================================================================================
*/
?>
<section class="projects">
    <div class="container">
        <h1><i class="fas fa-folder-open"></i> Mijn Projecten</h1>
        <p class="section-intro">Een overzicht van mijn recente projecten en bijdragen</p>
        <div class="project-filters">
            <button class="filter-btn active" data-filter="all"><i class="fas fa-th"></i> Alle</button>
            <button class="filter-btn" data-filter="minecraft"><i class="fas fa-cube"></i> Minecraft</button>
            <button class="filter-btn" data-filter="web"><i class="fas fa-globe"></i> Web</button>
            <button class="filter-btn" data-filter="api"><i class="fas fa-code"></i> API</button>
        </div>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card" data-category="<?= htmlspecialchars($project['category']) ?>">
                <?php if (!empty($project['image'])): ?>
                <div class="project-image-wrapper">
                    <img src="<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="project-image" onerror="this.src='public/images/placeholder.jpg'">
                </div>
                <?php endif; ?>
                <div class="project-content">
                    <h3><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['description']) ?></p>
                    <div class="tech-stack">
                        <?php foreach ($project['tech'] as $tech): ?>
                        <span class="tech-tag"><?= htmlspecialchars($tech) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="project-links">
                        <?php if (!empty($project['repo_url'])): ?>
                        <a href="<?= htmlspecialchars($project['repo_url']) ?>" target="_blank" class="btn btn-secondary"><i class="fab fa-github"></i> Code</a>
                        <?php endif; ?>
                            <?php if (!empty($project['demo_url'])): ?>
                        <a href="<?= htmlspecialchars($project['demo_url']) ?>" target="_blank" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> Demo</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>