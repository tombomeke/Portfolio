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
        <h1><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>
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
</script>
</body>
</html>
