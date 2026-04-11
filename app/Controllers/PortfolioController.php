<?php
// /app/Controllers/PortfolioController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/translations.php';
require_once __DIR__ . '/../Models/ContactMessageModel.php';

require_once __DIR__ . '/../Models/ProjectModels.php';
require_once __DIR__ . '/../Models/SkillModel.php';
require_once __DIR__ . '/../Models/GameStatsModel.php';
require_once __DIR__ . '/../Models/NewsModel.php';
require_once __DIR__ . '/../Models/FaqModel.php';

class PortfolioController {
    private $projectModel;
    private $newsModel;
    private $faqModel;
    private $skillModel;
    private $gameStatsModel;
    private $contactModel;
    private $contactRecipient = 'tom1dekoning@gmail.com';

    public function __construct() {
        $this->projectModel   = new ProjectModel();
        $this->skillModel     = new SkillModel();
        $this->gameStatsModel = new GameStatsModel();
        $this->newsModel      = new NewsModel();
        $this->faqModel       = new FaqModel();
        $this->contactModel   = new ContactMessageModel();
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
        try {
            $lang = Translations::getCurrentLang();
            $skills         = $this->skillModel->getAllSkills();
            $education      = $this->skillModel->getEducation($lang);
            $learning_goals = $this->skillModel->getLearningGoals($lang);
        } catch (\Throwable $e) {
            $skills = $education = $learning_goals = [];
            $lang   = 'nl';
        }

        $data = [
            'title'          => trans('nav_devlife'),
            'skills'         => $skills,
            'skillModel'     => $this->skillModel,
            'education'      => $education,
            'learning_goals' => $learning_goals,
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

        if (!function_exists('mail')) {
            $_SESSION['contact_error'] = 'Mailfunctie is niet beschikbaar op deze server. Mail direct naar ' . $this->contactRecipient;
            header('Location: ?page=contact');
            exit;
        }

        // Send email using a trusted sender and user Reply-To for better deliverability.
        $to = $this->contactRecipient;
        $subject = 'Portfolio Contact: ' . $name;
        $emailBody = "Naam: $name\nE-mail: $email\n\nBericht:\n$message";
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $serverName = preg_replace('/^www\./i', '', $serverName);
        $fromAddress = 'noreply@' . $serverName;

        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $fromAddress = 'noreply@localhost.localdomain';
        }

        $headers = [];
        $headers[] = 'From: Portfolio Website <' . $fromAddress . '>';
        $headers[] = 'Reply-To: ' . $email;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        $headerString = implode("\r\n", $headers);

        // Always save to database so nothing is lost
        $this->contactModel->save(trim($postData['name']), trim($postData['email']), trim($postData['message']));

        if (mail($to, $subject, $emailBody, $headerString)) {
            $_SESSION['contact_success'] = 'Bericht succesvol verzonden! Ik neem zo snel mogelijk contact met je op.';
        } else {
            $_SESSION['contact_success'] = 'Bericht ontvangen! Ik neem zo snel mogelijk contact met je op.';
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

    public function showNews() {
        $lang    = Translations::getCurrentLang();
        $perPage = 9;
        $page    = max(1, (int) ($_GET['p'] ?? 1));
        $offset  = ($page - 1) * $perPage;
        $total   = $this->newsModel->count($lang);
        $items   = $this->newsModel->getAll($lang, $perPage, $offset);

        $this->render('news', [
            'title'      => 'News',
            'items'      => $items,
            'page'       => $page,
            'totalPages' => (int) ceil($total / $perPage),
        ]);
    }

    public function showNewsItem($id) {
        $lang = Translations::getCurrentLang();
        $item = $this->newsModel->getById($id, $lang);

        if (!$item) {
            $this->show404();
            return;
        }

        $this->render('news-item', [
            'title' => htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'),
            'item'  => $item,
        ]);
    }

    public function showFaq() {
        $lang = Translations::getCurrentLang();
        $this->render('faq', [
            'title'      => 'FAQ',
            'categories' => $this->faqModel->getAllWithItems($lang),
        ]);
    }

    public function showReadmeSync() {
        $apiUrl  = 'https://tombomekestudio.com/api/readmesync/generate';
        $repoUrl = isset($_GET['repo']) ? trim($_GET['repo']) : '';

        $result   = null;
        $error    = null;
        $language = null;

        if ($repoUrl !== '') {
            if (!$this->isValidGitHubUrl($repoUrl)) {
                $error = 'Ongeldige GitHub URL. Verwacht: https://github.com/owner/repo';
            } else {
                $payload = json_encode(['githubRepoUrl' => $repoUrl]);

                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 35,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $raw      = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
                curl_close($ch);

                if ($curlErr) {
                    $error = 'De ReadmeSync API is momenteel niet bereikbaar.';
                } elseif ($httpCode === 200) {
                    $data     = json_decode($raw, true);
                    $result   = $data['content']  ?? null;
                    $language = $data['language'] ?? null;
                } elseif ($httpCode === 404) {
                    $error = 'Repository niet gevonden of is privé.';
                } else {
                    $decoded = json_decode($raw, true);
                    $error   = $decoded['detail'] ?? $decoded['error'] ?? 'Er is een fout opgetreden bij het genereren.';
                }
            }
        }

        $this->render('readmesync', [
            'title'    => 'ReadmeSync – Live Code Overview',
            'repoUrl'  => htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8'),
            'result'   => $result,
            'language' => $language,
            'error'    => $error,
        ]);
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

    private function isValidGitHubUrl($url) {
        if (empty($url)) return false;
        $parsed = parse_url($url);
        return isset($parsed['scheme'], $parsed['host'], $parsed['path'])
            && in_array($parsed['scheme'], ['https', 'http'], true)
            && $parsed['host'] === 'github.com'
            && substr_count(trim($parsed['path'], '/'), '/') >= 1;
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
