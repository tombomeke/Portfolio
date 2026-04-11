<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> – tombomeke admin</title>
    <link rel="stylesheet" href="public/css/admin.css">
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
        <span>Admin panel</span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Content</div>
        <a href="?page=admin" class="sidebar-link <?= ($currentSection === 'dashboard') ? 'active' : '' ?>">
            <i class="fas fa-gauge-high fa-fw"></i> Dashboard
        </a>
        <a href="?page=admin&section=news" class="sidebar-link <?= ($currentSection === 'news') ? 'active' : '' ?>">
            <i class="fas fa-newspaper fa-fw"></i> News
        </a>
        <a href="?page=admin&section=faq" class="sidebar-link <?= ($currentSection === 'faq') ? 'active' : '' ?>">
            <i class="fas fa-circle-question fa-fw"></i> FAQ
        </a>
        <a href="?page=admin&section=projects" class="sidebar-link <?= ($currentSection === 'projects') ? 'active' : '' ?>">
            <i class="fas fa-folder-open fa-fw"></i> Projects
        </a>
        <a href="?page=admin&section=dev-life" class="sidebar-link <?= ($currentSection === 'dev-life') ? 'active' : '' ?>">
            <i class="fas fa-laptop-code fa-fw"></i> Dev Life
        </a>
        <a href="?page=admin&section=tags" class="sidebar-link <?= ($currentSection === 'tags') ? 'active' : '' ?>">
            <i class="fas fa-tags fa-fw"></i> Tags
        </a>
        <a href="?page=admin&section=comments" class="sidebar-link <?= ($currentSection === 'comments') ? 'active' : '' ?>">
            <i class="fas fa-comments fa-fw"></i> Reacties
            <?php if (($pendingComments ?? 0) > 0): ?>
                <span class="badge"><?= $pendingComments ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-label" style="margin-top:.75rem">Inbox</div>
        <a href="?page=admin&section=contact" class="sidebar-link <?= ($currentSection === 'contact') ? 'active' : '' ?>">
            <i class="fas fa-envelope fa-fw"></i> Contact
            <?php if (($unreadMessages ?? 0) > 0): ?>
                <span class="badge"><?= $unreadMessages ?></span>
            <?php endif; ?>
        </a>

        <?php if (isset($authUser) && $authUser['role'] === 'owner'): ?>
        <div class="nav-section-label" style="margin-top:.75rem">Owner</div>
        <a href="?page=admin&section=users" class="sidebar-link <?= ($currentSection === 'users') ? 'active' : '' ?>">
            <i class="fas fa-users fa-fw"></i> Gebruikers
        </a>
        <?php endif; ?>

        <?php if (isset($authUser) && $authUser['role'] === 'owner'): ?>
        <div class="nav-section-label" style="margin-top:.75rem">Systeem</div>
        <a href="?page=admin&section=settings" class="sidebar-link <?= ($currentSection === 'settings') ? 'active' : '' ?>">
            <i class="fas fa-sliders-h fa-fw"></i> Instellingen
        </a>
        <a href="?page=admin&section=wip" class="sidebar-link <?= ($currentSection === 'wip') ? 'active' : '' ?>">
            <i class="fas fa-hard-hat fa-fw"></i> WIP Pagina's
        </a>
        <a href="?page=admin&section=roadmap" class="sidebar-link <?= ($currentSection === 'roadmap') ? 'active' : '' ?>">
            <i class="fas fa-list-check fa-fw"></i> Roadmap
        </a>
        <a href="?page=admin&section=activity-logs" class="sidebar-link <?= ($currentSection === 'activity-logs') ? 'active' : '' ?>">
            <i class="fas fa-history fa-fw"></i> Activity Log
        </a>
        <a href="?page=admin&section=telemetry" class="sidebar-link <?= ($currentSection === 'telemetry') ? 'active' : '' ?>">
            <i class="fas fa-chart-line fa-fw"></i> Telemetry
        </a>
        <?php endif; ?>

        <div class="nav-section-label" style="margin-top:.75rem">Account</div>
        <a href="?page=admin&section=profile" class="sidebar-link <?= ($currentSection === 'profile') ? 'active' : '' ?>">
            <i class="fas fa-user-circle fa-fw"></i> Mijn profiel
        </a>

        <div class="nav-section-label" style="margin-top:.75rem">Site</div>
        <a href="?page=home" target="_blank" class="sidebar-link">
            <i class="fas fa-arrow-up-right-from-square fa-fw"></i> Bekijk site
        </a>
    </nav>

    <div class="sidebar-footer">
        Ingelogd als <strong><?= htmlspecialchars($authUser['username'] ?? '') ?></strong><br>
        <a href="?page=admin&section=logout">Uitloggen</a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" type="button" aria-label="Open menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>
        </div>
        <div class="topbar-right">
            <span class="role-badge <?= htmlspecialchars($authUser['role'] ?? 'admin') ?>">
                <?= htmlspecialchars($authUser['role'] ?? 'admin') ?>
            </span>
            <?= htmlspecialchars($authUser['username'] ?? '') ?>
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

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
});

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
