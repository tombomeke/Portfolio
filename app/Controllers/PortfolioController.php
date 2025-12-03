<?php
// /app/Controllers/PortfolioController.php
session_start();

require_once __DIR__ . '/../Config/translations.php';

require_once __DIR__ . '/../Models/ProjectModels.php';
require_once __DIR__ . '/../Models/SkillModel.php';
require_once __DIR__ . '/../Models/GameStatsModel.php';

class PortfolioController {
    private $projectModel;
    private $skillModel;
    private $gameStatsModel;

    public function __construct() {
        $this->projectModel = new ProjectModel();
        $this->skillModel = new SkillModel();
        $this->gameStatsModel = new GameStatsModel();
    }

    public function showAbout() {
        $data = [
            'title' => trans('nav_about'),
            'name' => 'Tom Dekoning',
            'email' => 'tom1dekoning@gmail.com',
            'linkedin' => 'https://www.linkedin.com/in/tom-dekoning-567523352/',
            'github' => 'https://github.com/tombomeke'
        ];
        $this->render('about', $data);
    }

    public function showDevLife() {
        $skills = $this->skillModel->getAllSkills();

        // Get current language
        $lang = Translations::getCurrentLang();

        // Education items (translatable)
        $education = [
            'nl' => [
                'HBO ICT - Hogeschool van Amsterdam (2023-heden)',
                'Java Certification - Oracle (2023)',
                'PHP & MySQL - Codecademy (2022)'
            ],
            'en' => [
                'HBO ICT - University of Applied Sciences Amsterdam (2023-present)',
                'Java Certification - Oracle (2023)',
                'PHP & MySQL - Codecademy (2022)'
            ]
        ];

        // Learning goals (translatable)
        $learning_goals = [
            'nl' => [
                'Laravel Framework diepgaand leren',
                'React.js voor moderne frontends',
                'Docker & DevOps automation',
                'Advanced design patterns'
            ],
            'en' => [
                'Learn Laravel Framework in-depth',
                'React.js for modern frontends',
                'Docker & DevOps automation',
                'Advanced design patterns'
            ]
        ];

        $data = [
            'title' => trans('nav_devlife'),
            'skills' => $skills,
            'skillModel' => $this->skillModel,
            'education' => $education[$lang],
            'learning_goals' => $learning_goals[$lang]
        ];
        $this->render('dev-life', $data);
    }

    public function showGames() {
        $mcStats = $this->gameStatsModel->getMinecraftStats();
        $r6Stats = $this->gameStatsModel->getR6Stats();

        $data = [
            'title' => 'Gaming Stats',
            'minecraft' => $mcStats,
            'r6siege' => $r6Stats
        ];
        $this->render('games', $data);
    }

    public function showWIP($page = '') {
        $data = [
            'title' => trans('wip_page_title'),
            'pageLabel' => $this->getPageLabel($page),
            'pageKey' => $page
        ];
        $this->render('WIP', $data);
    }

    public function showProjects() {
        $projects = $this->projectModel->getAllProjects();
        $data = [
            'title' => 'Projecten',
            'projects' => $projects,
            'projectModel' => $this->projectModel
        ];
        $this->render('projects', $data);
    }

    public function showContact() {
        $data = [
            'title' => 'Contact',
            'success' => $_SESSION['contact_success'] ?? false,
            'error' => $_SESSION['contact_error'] ?? false
        ];
        unset($_SESSION['contact_success'], $_SESSION['contact_error']);
        $this->render('contact', $data);
    }

    public function handleContact($postData) {
        // Validate input
        $name = $this->sanitizeInput($postData['name'] ?? '');
        $email = $this->sanitizeInput($postData['email'] ?? '');
        $message = $this->sanitizeInput($postData['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['contact_error'] = 'Alle velden zijn verplicht.';
            header('Location: ?page=contact');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_error'] = 'Ongeldig e-mailadres.';
            header('Location: ?page=contact');
            exit;
        }

        if (strlen($name) < 2) {
            $_SESSION['contact_error'] = 'Naam moet minimaal 2 karakters bevatten.';
            header('Location: ?page=contact');
            exit;
        }

        if (strlen($message) < 10) {
            $_SESSION['contact_error'] = 'Bericht moet minimaal 10 karakters bevatten.';
            header('Location: ?page=contact');
            exit;
        }

        // Send email (simplified - configure for production)
        $to = 'jouw@email.com';
        $subject = 'Portfolio Contact: ' . $name;
        $emailBody = "Naam: $name\nE-mail: $email\n\nBericht:\n$message";
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $emailBody, $headers)) {
            $_SESSION['contact_success'] = 'Bericht succesvol verzonden! Ik neem zo snel mogelijk contact met je op.';
        } else {
            $_SESSION['contact_error'] = 'Er is een fout opgetreden bij het verzenden. Probeer het later opnieuw of mail direct naar ' . $to;
        }

        header('Location: ?page=contact');
        exit;
    }

    public function downloadCV() {
        $file = __DIR__ . '/../../public/files/CV_JouwNaam.pdf';

        if (file_exists($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="CV_JouwNaam.pdf"');
            header('Content-Length: ' . filesize($file));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            readfile($file);
            exit;
        } else {
            $this->show404();
        }
    }

    public function show404() {
        http_response_code(404);
        $data = ['title' => '404 - Pagina niet gevonden'];
        $this->render('404', $data);
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        $viewFile = __DIR__ . "/../Views/{$view}.php";

        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<h1>View niet gevonden: {$view}</h1>";
        }

        $content = ob_get_clean();
        include __DIR__ . '/../Views/layout.php';
    }

    private function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private function getPageLabel($page) {
        $labels = [
            'home' => trans('nav_about'),
            'about' => trans('nav_about'),
            'dev-life' => trans('nav_devlife'),
            'games' => trans('nav_games'),
            'projects' => trans('nav_projects'),
            'contact' => trans('nav_contact')
        ];

        if (isset($labels[$page])) {
            return $labels[$page];
        }

        if (!empty($page)) {
            return ucwords(str_replace(['-', '_'], ' ', $page));
        }

        return trans('wip_default_page_name');
    }
}
?>
