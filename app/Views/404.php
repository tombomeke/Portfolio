<?php
/*
================================================================================
BESTAND 7: /app/Views/404.php
================================================================================
*/
?>
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-animation"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="error-code">404</div>
            <h1><?= trans('error_404_title') ?></h1>
            <p class="error-message"><?= trans('error_404_message') ?></p>
            <div class="error-actions">
                <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> <?= trans('error_404_home') ?></a>
                <a href="?page=projects" class="btn btn-secondary"><i class="fas fa-folder"></i> <?= trans('error_404_projects') ?></a>
            </div>
            <div class="error-suggestions">
                <h3><?= trans('error_404_suggestions') ?></h3>
                <ul>
                    <li><a href="?page=dev-life"><i class="fas fa-code"></i> <?= trans('nav_devlife') ?></a></li>
                    <li><a href="?page=games"><i class="fas fa-gamepad"></i> <?= trans('games_title') ?></a></li>
                    <li><a href="?page=contact"><i class="fas fa-envelope"></i> <?= trans('nav_contact') ?></a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php