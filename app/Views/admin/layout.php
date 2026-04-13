<?php
$adminStyleVersion = (string) (@filemtime(__DIR__ . '/../../../public/css/admin.css') ?: time());
$currentLang = Translations::getCurrentLang();
$currentAdminUrl = '?page=admin';
if (!empty($_GET['section']) && $_GET['section'] !== 'dashboard') {
    $currentAdminUrl .= '&section=' . urlencode((string) $_GET['section']);
}
if (!empty($_GET['action'])) {
    $currentAdminUrl .= '&action=' . urlencode((string) $_GET['action']);
}
if (!empty($_GET['id'])) {
    $currentAdminUrl .= '&id=' . urlencode((string) $_GET['id']);
}

// TODO(responsive): [P2][done] Keep admin sidebar navigation scrollable without overlapping footer items.
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> – tombomeke admin</title>
    <link rel="stylesheet" href="public/css/admin.css?v=<?= htmlspecialchars($adminStyleVersion ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="robots" content="noindex,nofollow">
</head>
<body>

<?php
// Determine active section for sidebar highlighting
$currentSection = $_GET['section'] ?? 'dashboard';
$isAuth = isset($authUser);
?>

<?php if ($isAuth): ?>
<aside class="admin-sidebar">
    <a href="?page=admin" class="sidebar-brand">
        tombomeke
        <span><?= trans('admin_panel') ?></span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section-label"><?= trans('admin_nav_content') ?></div>
        <a href="?page=admin" class="sidebar-link <?= ($currentSection === 'dashboard') ? 'active' : '' ?>">
            <i class="fas fa-gauge-high fa-fw"></i> <?= trans('nav_dashboard') ?>
        </a>
        <a href="?page=admin&section=news" class="sidebar-link <?= ($currentSection === 'news') ? 'active' : '' ?>">
            <i class="fas fa-newspaper fa-fw"></i> <?= trans('admin_news') ?>
        </a>
        <a href="?page=admin&section=faq" class="sidebar-link <?= ($currentSection === 'faq') ? 'active' : '' ?>">
            <i class="fas fa-circle-question fa-fw"></i> <?= trans('nav_faq') ?>
        </a>
        <a href="?page=admin&section=projects" class="sidebar-link <?= ($currentSection === 'projects') ? 'active' : '' ?>">
            <i class="fas fa-folder-open fa-fw"></i> <?= trans('admin_projects') ?>
        </a>
        <a href="?page=admin&section=dev-life" class="sidebar-link <?= ($currentSection === 'dev-life') ? 'active' : '' ?>">
            <i class="fas fa-laptop-code fa-fw"></i> <?= trans('admin_dev_life') ?>
        </a>
        <a href="?page=admin&section=tags" class="sidebar-link <?= ($currentSection === 'tags') ? 'active' : '' ?>">
            <i class="fas fa-tags fa-fw"></i> <?= trans('admin_tags') ?>
        </a>
        <a href="?page=admin&section=comments" class="sidebar-link <?= ($currentSection === 'comments') ? 'active' : '' ?>">
            <i class="fas fa-comments fa-fw"></i> <?= trans('admin_comments') ?>
            <?php if (($pendingComments ?? 0) > 0): ?>
                <span class="badge"><?= $pendingComments ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-label" style="margin-top:.75rem"><?= trans('admin_nav_inbox') ?></div>
        <a href="?page=admin&section=contact" class="sidebar-link <?= ($currentSection === 'contact') ? 'active' : '' ?>">
            <i class="fas fa-envelope fa-fw"></i> <?= trans('admin_contact') ?>
            <?php if (($unreadMessages ?? 0) > 0): ?>
                <span class="badge"><?= $unreadMessages ?></span>
            <?php endif; ?>
        </a>

        <?php if (isset($authUser) && $authUser['role'] === 'owner'): ?>
        <div class="nav-section-label" style="margin-top:.75rem"><?= trans('admin_nav_owner') ?></div>
        <a href="?page=admin&section=users" class="sidebar-link <?= ($currentSection === 'users') ? 'active' : '' ?>">
            <i class="fas fa-users fa-fw"></i> <?= trans('admin_users') ?>
        </a>
        <?php endif; ?>

        <?php if (isset($authUser) && $authUser['role'] === 'owner'): ?>
        <div class="nav-section-label" style="margin-top:.75rem"><?= trans('admin_nav_system') ?></div>
        <a href="?page=admin&section=settings" class="sidebar-link <?= ($currentSection === 'settings') ? 'active' : '' ?>">
            <i class="fas fa-sliders-h fa-fw"></i> <?= trans('admin_settings') ?>
        </a>
        <a href="?page=admin&section=wip" class="sidebar-link <?= ($currentSection === 'wip') ? 'active' : '' ?>">
            <i class="fas fa-hard-hat fa-fw"></i> <?= trans('admin_wip_pages') ?>
        </a>
        <a href="?page=admin&section=roadmap" class="sidebar-link <?= ($currentSection === 'roadmap') ? 'active' : '' ?>">
            <i class="fas fa-list-check fa-fw"></i> <?= trans('admin_roadmap') ?>
        </a>
        <a href="?page=admin&section=activity-logs" class="sidebar-link <?= ($currentSection === 'activity-logs') ? 'active' : '' ?>">
            <i class="fas fa-history fa-fw"></i> <?= trans('admin_activity_log') ?>
        </a>
        <a href="?page=admin&section=telemetry" class="sidebar-link <?= ($currentSection === 'telemetry') ? 'active' : '' ?>">
            <i class="fas fa-chart-line fa-fw"></i> <?= trans('admin_telemetry') ?>
        </a>
        <?php endif; ?>

        <div class="nav-section-label" style="margin-top:.75rem"><?= trans('admin_nav_account') ?></div>
        <a href="?page=admin&section=profile" class="sidebar-link <?= ($currentSection === 'profile') ? 'active' : '' ?>">
            <i class="fas fa-user-circle fa-fw"></i> <?= trans('admin_my_profile') ?>
        </a>
        <a href="?page=profile&u=<?= urlencode((string) ($authUser['username'] ?? '')) ?>" target="_blank" rel="noopener" class="sidebar-link">
            <i class="fas fa-id-badge fa-fw"></i> <?= trans('admin_open_public_profile') ?>
        </a>

        <div class="nav-section-label" style="margin-top:.75rem"><?= trans('admin_nav_site') ?></div>
        <a href="?page=home" target="_blank" class="sidebar-link">
            <i class="fas fa-arrow-up-right-from-square fa-fw"></i> <?= trans('admin_view_site') ?>
        </a>
    </nav>

    <div class="sidebar-footer">
        <?= trans('admin_logged_in_as') ?> <strong><?= htmlspecialchars($authUser['username'] ?? '') ?></strong><br>
        <a href="?page=admin&section=logout"><?= trans('nav_logout') ?></a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" type="button" aria-label="<?= trans('admin_open_menu') ?>" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>
        </div>
        <div class="topbar-right">
            <div class="topbar-lang-switch" role="group" aria-label="<?= htmlspecialchars(trans('admin_language_switch')) ?>">
                <a href="?page=admin&section=language&lang=nl&back=<?= urlencode($currentAdminUrl) ?>" class="topbar-lang-link <?= $currentLang === 'nl' ? 'active' : '' ?>">NL</a>
                <a href="?page=admin&section=language&lang=en&back=<?= urlencode($currentAdminUrl) ?>" class="topbar-lang-link <?= $currentLang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
            <a href="?page=profile&u=<?= urlencode((string) ($authUser['username'] ?? '')) ?>" class="topbar-profile-link" target="_blank" rel="noopener" title="<?= htmlspecialchars(trans('admin_open_public_profile')) ?>">
                <i class="fas fa-id-badge"></i>
            </a>
            <span class="role-badge <?= htmlspecialchars($authUser['role'] ?? 'admin') ?>">
                <?= htmlspecialchars($authUser['role'] ?? 'admin') ?>
            </span>
            <span class="topbar-username" title="<?= htmlspecialchars($authUser['username'] ?? '') ?>">
                <?= htmlspecialchars($authUser['username'] ?? '') ?>
            </span>
        </div>
    </header>

    <main class="admin-content">
        <?= $content ?>
    </main>
</div>

<?php else: ?>
<?= $content ?>
<?php endif; ?>

<?php if ($isAuth): ?>
<div class="admin-backdrop" aria-hidden="true"></div>
<div class="confirm-modal" id="confirmModal" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <div class="confirm-modal__panel">
        <h3 id="confirmModalTitle" class="confirm-modal__title"><?= htmlspecialchars(trans('admin_confirm_title')) ?></h3>
        <p class="confirm-modal__message" id="confirmModalMessage"><?= htmlspecialchars(trans('admin_confirm_message')) ?></p>
        <div class="confirm-modal__actions">
            <button type="button" class="btn btn-ghost" id="confirmModalCancel"><?= htmlspecialchars(trans('admin_cancel')) ?></button>
            <button type="button" class="btn btn-danger" id="confirmModalOk"><?= htmlspecialchars(trans('admin_confirm')) ?></button>
        </div>
    </div>
</div>
<?php endif; ?>
<script>
// Language tab switcher (used on create/edit forms)
document.querySelectorAll('.lang-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        const group = tab.closest('.lang-tab-group') || document.body;
        group.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const target = tab.dataset.target;
        document.querySelectorAll('.lang-panel').forEach(p => {
            p.classList.toggle('active', p.id === target);
        });
    });
});

// Confirm actions (custom modal)
const confirmModal = document.getElementById('confirmModal');
const confirmModalMessage = document.getElementById('confirmModalMessage');
const confirmModalOk = document.getElementById('confirmModalOk');
const confirmModalCancel = document.getElementById('confirmModalCancel');
let confirmAction = null;

const closeConfirmModal = () => {
    if (!confirmModal) return;
    confirmModal.classList.remove('is-open');
    confirmModal.setAttribute('aria-hidden', 'true');
    confirmModal.hidden = true;
    document.body.style.overflow = '';
    confirmAction = null;
};

const openConfirmModal = (message, action) => {
    if (!confirmModal || !confirmModalMessage) return;
    confirmModalMessage.textContent = message || <?= json_encode(trans('admin_confirm_message')) ?>;
    confirmAction = action;
    confirmModal.hidden = false;
    confirmModal.classList.add('is-open');
    confirmModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
};

if (confirmModal && confirmModalOk && confirmModalCancel) {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            const message = el.dataset.confirm || <?= json_encode(trans('admin_confirm_message')) ?>;
            openConfirmModal(message, () => {
                const form = el.closest('form');
                if (form) {
                    form.submit();
                    return;
                }
                if (el.tagName === 'A' && el.href) {
                    window.location.href = el.href;
                }
            });
        });
    });

    confirmModalOk.addEventListener('click', () => {
        const action = confirmAction;
        closeConfirmModal();
        if (typeof action === 'function') {
            action();
        }
    });

    confirmModalCancel.addEventListener('click', closeConfirmModal);

    confirmModal.addEventListener('click', (e) => {
        if (e.target === confirmModal) {
            closeConfirmModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && confirmModal.classList.contains('is-open')) {
            closeConfirmModal();
        }
    });
}

// Mobile sidebar toggle
const sidebar = document.querySelector('.admin-sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const backdrop = document.querySelector('.admin-backdrop');

if (sidebar && sidebarToggle && backdrop) {
    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
        sidebarToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    };

    sidebarToggle.addEventListener('click', () => {
        if (sidebar.classList.contains('is-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    backdrop.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
}
</script>
</body>
</html>
