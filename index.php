<?php
// index.php — Main entry point

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = $_GET['page'] ?? 'home';

// ── Admin & setup routes (handled by AdminController) ─────────────────────
if ($page === 'admin' || $page === 'setup') {
    require_once 'app/Controllers/AdminController.php';
    (new AdminController())->dispatch($page);
    exit;
}

// ── Public routes ─────────────────────────────────────────────────────────
require_once 'app/Controllers/PortfolioController.php';
$controller = new PortfolioController();

// Pages temporarily showing WIP
$wipPages = []; // Add page slugs here to redirect to WIP view
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
    default:
        $controller->show404();
}
