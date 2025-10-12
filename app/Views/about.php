<?php
/*
================================================================================
BESTAND 2: /app/Views/about.php
================================================================================
*/
?>
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><span data-translate="hero_greeting"><?= trans('hero_greeting') ?></span> <?= htmlspecialchars($name) ?></h1>
                <p class="lead" data-translate="hero_intro"><?= trans('hero_intro') ?></p>
                <div class="hero-actions">
                    <a href="?page=projects" class="btn btn-primary">
                        <i class="fas fa-folder-open"></i> <span data-translate="hero_view_work"><?= trans('hero_view_work') ?></span>
                    </a>
                    <a href="?page=download-cv" class="btn btn-secondary">
                        <i class="fas fa-download"></i> <span data-translate="hero_download_cv"><?= trans('hero_download_cv') ?></span>
                    </a>
                </div>
                <div class="contact-info">
                    <a href="mailto:<?= htmlspecialchars($email) ?>">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?>
                    </a>
                    <a href="<?= htmlspecialchars($linkedin) ?>" target="_blank">
                        <i class="fab fa-linkedin"></i> LinkedIn
                    </a>
                    <a href="<?= htmlspecialchars($github) ?>" target="_blank">
                        <i class="fab fa-github"></i> GitHub
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="public/images/profile.png" alt="<?= htmlspecialchars($name) ?>" class="profile-img">
            </div>
        </div>
    </div>
</section>