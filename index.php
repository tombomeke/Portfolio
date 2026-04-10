<?php
// /index.php - Main entry point for shared hosting
require_once 'app/Controllers/PortfolioController.php';

$controller = new PortfolioController();
$page = $_GET['page'] ?? 'home';

// Pages that should temporarily show the WIP screen
$wipPages = ['games']; // Add a page slug here to send it to the WIP view

if (in_array($page, $wipPages, true)) {
    $controller->showWIP($page);
    exit;
}

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
    case 'readmesync':
        $controller->showReadmeSync();
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
