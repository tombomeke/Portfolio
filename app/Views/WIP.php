<section class="wip-section">
    <div class="container">
        <div class="wip-card">
            <div class="wip-badge">
                <i class="fas fa-wrench"></i>
                <?= trans('wip_page_title') ?>
            </div>

            <h1><?= trans('wip_heading') ?></h1>
            <p class="wip-lead">
                <?= str_replace('{page}', htmlspecialchars($pageLabel ?? trans('wip_default_page_name')), trans('wip_intro')) ?>
            </p>
            <p class="wip-note"><?= trans('wip_secondary') ?></p>

            <div class="wip-meta">
                <span class="wip-chip"><?= trans('wip_status_badge') ?></span>
                <span class="wip-chip"><?= htmlspecialchars($pageLabel ?? trans('wip_default_page_name')) ?></span>
            </div>

            <div class="wip-actions">
                <a href="?page=home" class="btn btn-primary">
                    <i class="fas fa-home"></i> <?= trans('wip_back_home') ?>
                </a>
                <a href="?page=projects" class="btn btn-secondary">
                    <i class="fas fa-folder-open"></i> <?= trans('wip_view_projects') ?>
                </a>
            </div>

            <a class="wip-contact" href="?page=contact">
                <i class="fas fa-comment-dots"></i>
                <?= trans('wip_contact') ?>
            </a>

            <p class="wip-feedback"><?= trans('wip_feedback') ?></p>
        </div>
    </div>
</section>
