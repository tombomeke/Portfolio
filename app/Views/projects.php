<?php
/*
================================================================================
BESTAND: /app/Views/projects.php (UPDATED with Modal Support)
================================================================================
*/
?>
<section class="projects">
    <div class="container">
        <h1><i class="fas fa-folder-open"></i> <span data-translate="projects_title"><?= trans('projects_title') ?></span></h1>
        <p class="section-intro" data-translate="projects_intro"><?= trans('projects_intro') ?></p>
        <p class="section-hint"><i class="fas fa-hand-pointer"></i> <span data-translate="projects_click_details"><?= trans('projects_click_details') ?></span></p>

        <div class="project-filters">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-th"></i> <span data-translate="projects_filter_all"><?= trans('projects_filter_all') ?></span>
            </button>
            <button class="filter-btn" data-filter="minecraft">
                <i class="fas fa-cube"></i> <span data-translate="projects_filter_minecraft"><?= trans('projects_filter_minecraft') ?></span>
            </button>
            <button class="filter-btn" data-filter="web">
                <i class="fas fa-globe"></i> <span data-translate="projects_filter_web"><?= trans('projects_filter_web') ?></span>
            </button>
            <button class="filter-btn" data-filter="api">
                <i class="fas fa-code"></i> <span data-translate="projects_filter_api"><?= trans('projects_filter_api') ?></span>
            </button>
            <button class="filter-btn" data-filter="cli">
                <i class="fas fa-code"></i> <span data-translate="projects_filter_cli"><?= trans('projects_filter_cli') ?></span>
            </button>
        </div>

        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
                <div class="project-card"
                     data-category="<?= htmlspecialchars($project['category']) ?>"
                     data-modal='<?= $projectModel->getModalData($project) ?>'>

                    <?php if (!empty($project['image'])): ?>
                        <div class="project-image-wrapper">
                            <img src="<?= htmlspecialchars($project['image']) ?>"
                                 alt="<?= htmlspecialchars($project['title']) ?>"
                                 class="project-image"
                                 onerror="this.src='public/images/placeholder.jpg'">
                            <div class="project-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
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

                        <div class="project-links" onclick="event.stopPropagation()">
                            <?php if (!empty($project['repo_url'])): ?>
                                <a href="<?= htmlspecialchars($project['repo_url']) ?>"
                                   target="_blank"
                                   class="btn btn-secondary"
                                   onclick="event.stopPropagation()">
                                    <i class="fab fa-github"></i> <span data-translate="projects_view_code"><?= trans('projects_view_code') ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($project['demo_url'])): ?>
                                <a href="<?= htmlspecialchars($project['demo_url']) ?>"
                                   target="_blank"
                                   class="btn btn-primary"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-external-link-alt"></i> <span data-translate="projects_view_demo"><?= trans('projects_view_demo') ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    /* Project overlay for hover effect */
    .project-image-wrapper {
        position: relative;
        overflow: hidden;
    }

    .project-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(59, 130, 246, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .project-overlay i {
        font-size: 3rem;
        color: white;
        transform: scale(0.5);
        transition: transform 0.3s ease;
    }

    .project-card:hover .project-overlay {
        opacity: 1;
    }

    .project-card:hover .project-overlay i {
        transform: scale(1);
    }

    /* Prevent text selection when clicking */
    .project-card {
        user-select: none;
    }
</style>