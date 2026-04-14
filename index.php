<?php
// index.php — Main entry point

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || ((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    // TODO(security): done - Enforce secure session cookie flags (httponly, secure, samesite).
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$page = $_GET['page'] ?? 'home';

require_once 'app/Auth/Auth.php';
if (Auth::check()) {
    // Keep navbar/admin guards in sync with DB role changes (promote/demote).
    Auth::refreshSession();
}

// ── Admin & setup routes (handled by AdminController) ─────────────────────
if ($page === 'admin' || $page === 'setup') {
    // TODO(auth): done - Added router-level admin guard as defense-in-depth for guessed admin URLs.
    // Defense-in-depth: block direct access to admin routes for non-admin users.
    // Allow setup/login/logout routes to pass through to AdminController.
    if ($page === 'admin') {
        $section = $_GET['section'] ?? 'dashboard';
        $publicAdminSections = ['login', 'logout'];
        if (Auth::check()) {
            Auth::refreshSession();
        }
        if (!in_array($section, $publicAdminSections, true) && !Auth::isAdmin()) {
            header('Location: ?page=home');
            exit;
        }
    }

    require_once 'app/Controllers/AdminController.php';
    (new AdminController())->dispatch($page);
    exit;
}

// ── Public routes ─────────────────────────────────────────────────────────
require_once 'app/Controllers/PortfolioController.php';
$controller = new PortfolioController();

// TODO(ops): done - maintenance_mode gate added; public visitors see maintenance.php; admins/owners bypass it.
try {
    require_once 'app/Models/SiteSettingModel.php';
    if (SiteSettingModel::get('maintenance_mode', '0') === '1' && !Auth::isAdmin()) {
        http_response_code(503);
        require 'app/Views/maintenance.php';
        exit;
    }
} catch (\Throwable $e) {
    // SiteSettingModel or DB unavailable — skip maintenance gate
}

// Pages temporarily showing WIP — configured via admin panel (?page=admin&section=wip)
$wipPages = [];
$wipConfig = __DIR__ . '/app/Config/wip_pages.json';
if (file_exists($wipConfig)) {
    // TODO(config): [P2] validate wip_pages.json schema and log malformed JSON instead of silently falling back.
    $wipPages = json_decode(file_get_contents($wipConfig), true) ?? [];
}
if (in_array($page, $wipPages, true)) {
    $controller->showWIP($page);
    exit;
}

switch ($page) {
    case 'home':
    case 'about':
        $controller->showAbout();
        break;
    case 'dev-life':
        $controller->showDevLife();
        break;
    case 'games':
        $controller->showGames();
        break;
    case 'projects':
        $controller->showProjects();
        break;
    case 'project':
        $controller->showProjectDetail();
        break;
    case 'project-roadmaps':
        $controller->showProjectRoadmaps();
        break;
    case 'cron-sync-roadmaps':
        $controller->cronSyncRoadmaps();
        break;
    case 'news':
        $controller->showNews();
        break;
    case 'news-item':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'comment') {
            $controller->handleComment((int) ($_GET['id'] ?? 0));
        }
        $controller->showNewsItem((int) ($_GET['id'] ?? 0));
        break;
    case 'faq':
        $controller->showFaq();
        break;
    case 'readmesync':
        $controller->showReadmeSync();
        break;
    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handleContact($_POST);
        }
        $controller->showContact();
        break;
    case 'download-cv':
        $controller->downloadCV();
        break;
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handleLogin();
        }
        $controller->showLogin();
        break;
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handleRegister();
        }
        $controller->showRegister();
        break;
    case 'logout':
        $controller->handleLogout();
        break;
    case 'profile':
        $controller->showProfile($_GET['u'] ?? '');
        break;
    case 'settings':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handleSettings($_POST);
        }
        $controller->showSettings();
        break;
    default:
        $controller->show404();
}
