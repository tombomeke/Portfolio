<?php
// /public/index.php - Main entry point
require_once '../app/Controllers/PortfolioController.php';

$controller = new PortfolioController();
$page = $_GET['page'] ?? 'home';

// Route handling
switch($page) {
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
    case 'contact':
        if ($_POST) {
            $controller->handleContact($_POST);
        }
        $controller->showContact();
        break;
    case 'download-cv':
        $controller->downloadCV();
        break;
    default:
        $controller->show404();
}
?>