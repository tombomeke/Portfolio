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
require_once __DIR__ . '/../Models/NewsCommentModel.php';
require_once __DIR__ . '/../Models/SiteSettingModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Auth/Auth.php';

class PortfolioController {
    private $projectModel;
    private $newsModel;
    private $faqModel;
    private $skillModel;
    private $gameStatsModel;
    private $contactModel;
    private $commentModel;
    private $userModel;
    private $contactRecipient = 'tom1dekoning@gmail.com';

    public function __construct() {
        $this->projectModel   = new ProjectModel();
        $this->skillModel     = new SkillModel();
        $this->gameStatsModel = new GameStatsModel();
        $this->newsModel      = new NewsModel();
        $this->faqModel       = new FaqModel();
        $this->contactModel   = new ContactMessageModel();
        $this->commentModel   = new NewsCommentModel();
        $this->userModel      = new UserModel();
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
        $lang      = Translations::getCurrentLang();
        $perPage   = 9;
        $page      = max(1, (int) ($_GET['p'] ?? 1));
        $offset    = ($page - 1) * $perPage;
        $activeTag = trim($_GET['tag'] ?? '');
        $total     = $this->newsModel->count($lang, $activeTag ?: null);
        $items     = $this->newsModel->getAll($lang, $perPage, $offset, $activeTag ?: null);

        $this->render('news', [
            'title'      => 'News',
            'items'      => $items,
            'page'       => $page,
            'totalPages' => (int) ceil($total / $perPage),
            'activeTag'  => $activeTag,
        ]);
    }

    public function showNewsItem(int $id) {
        $lang = Translations::getCurrentLang();
        $item = $this->newsModel->getById($id, $lang);

        if (!$item) {
            $this->show404();
            return;
        }

        try {
            $comments        = $this->commentModel->getForNewsItem($id, true);
            $commentsEnabled = SiteSettingModel::get('comments_enabled', true);
        } catch (\Throwable $e) {
            $comments        = [];
            $commentsEnabled = false;
        }

        $this->render('news-item', [
            'title'           => htmlspecialchars($item['title']),
            'item'            => $item,
            'comments'        => $comments,
            'commentsEnabled' => $commentsEnabled,
        ]);
    }

    public function handleComment(int $newsItemId): void {
        $authUser = $_SESSION['auth_user'] ?? null;
        if (!$authUser) {
            header('Location: ?page=login&redirect=' . urlencode('?page=news-item&id=' . $newsItemId));
            exit;
        }

        if (!Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $_SESSION['comment_error'] = 'Ongeldig beveiligingstoken.';
            header('Location: ?page=news-item&id=' . $newsItemId);
            exit;
        }

        $body = trim($_POST['body'] ?? '');
        if (strlen($body) < 2 || strlen($body) > 2000) {
            $_SESSION['comment_error'] = 'Reactie moet tussen 2 en 2000 tekens zijn.';
            header('Location: ?page=news-item&id=' . $newsItemId);
            exit;
        }

        try {
            $requireApproval = SiteSettingModel::get('comments_require_approval', true);
            $isApproved      = !$requireApproval;
            $this->commentModel->create($newsItemId, (int) $authUser['id'], $body, $isApproved);
            $_SESSION['comment_success'] = $isApproved
                ? 'Reactie geplaatst.'
                : 'Reactie ingediend en wacht op goedkeuring.';
        } catch (\Throwable $e) {
            $_SESSION['comment_error'] = 'Reactie kon niet worden opgeslagen.';
        }

        header('Location: ?page=news-item&id=' . $newsItemId);
        exit;
    }

    public function showProfile(string $username): void {
        try {
            $user = $this->userModel->getByUsername($username);
        } catch (\Throwable $e) {
            $user = null;
        }

        if (!$user || !($user['public_profile'] ?? 1)) {
            $this->show404();
            return;
        }

        $this->render('profile', [
            'title'       => htmlspecialchars($user['username']) . '\'s profiel',
            'profileUser' => $user,
        ]);
    }

    public function showLogin(): void {
        if (Auth::check()) {
            header('Location: ?page=home');
            exit;
        }
        $redirect = $_GET['redirect'] ?? '';
        $this->render('login', [
            'title'    => 'Inloggen',
            'redirect' => $redirect,
            'error'    => $_SESSION['login_error'] ?? null,
        ]);
        unset($_SESSION['login_error']);
    }

    public function handleLogin(): void {
        if (!Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $_SESSION['login_error'] = 'Ongeldig beveiligingstoken.';
            header('Location: ?page=login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '';

        if (Auth::loginByEmail($email, $password)) {
            $user = Auth::user();
            // Admins/owners go to admin panel, regular users go back or home
            if (in_array($user['role'], ['owner', 'admin'], true)) {
                header('Location: ?page=admin');
            } elseif ($redirect && strpos($redirect, 'page=') !== false) {
                header('Location: ' . $redirect);
            } else {
                header('Location: ?page=home');
            }
            exit;
        }

        $_SESSION['login_error'] = 'Ongeldig e-mailadres of wachtwoord.';
        $qs = $redirect ? '&redirect=' . urlencode($redirect) : '';
        header('Location: ?page=login' . $qs);
        exit;
    }

    public function handleLogout(): void {
        Auth::logout();
        header('Location: ?page=home');
        exit;
    }

    public function showRegister(): void {
        if (Auth::check()) {
            header('Location: ?page=home');
            exit;
        }

        // Check if registration is enabled via site settings
        $enabled = true;
        try {
            $enabled = (bool) SiteSettingModel::get('registration_enabled', true);
        } catch (\Throwable $e) {}

        if (!$enabled) {
            $this->render('register-disabled', ['title' => 'Registratie uitgeschakeld']);
            return;
        }

        $this->render('register', [
            'title' => 'Registreren',
            'error' => $_SESSION['register_error'] ?? null,
            'old'   => $_SESSION['register_old'] ?? [],
        ]);
        unset($_SESSION['register_error'], $_SESSION['register_old']);
    }

    public function handleRegister(): void {
        // Check if registration is enabled
        $enabled = true;
        try {
            $enabled = (bool) SiteSettingModel::get('registration_enabled', true);
        } catch (\Throwable $e) {}

        if (!$enabled) {
            header('Location: ?page=register');
            exit;
        }

        if (!Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $_SESSION['register_error'] = 'Ongeldig beveiligingstoken.';
            header('Location: ?page=register');
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        $_SESSION['register_old'] = ['name' => $name, 'email' => $email];

        if (strlen($name) < 2) {
            $_SESSION['register_error'] = 'Naam moet minimaal 2 tekens bevatten.';
            header('Location: ?page=register');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Ongeldig e-mailadres.';
            header('Location: ?page=register');
            exit;
        }
        if ($this->userModel->emailExists($email)) {
            $_SESSION['register_error'] = 'Dit e-mailadres is al in gebruik.';
            header('Location: ?page=register');
            exit;
        }
        if (strlen($password) < 8) {
            $_SESSION['register_error'] = 'Wachtwoord moet minimaal 8 tekens bevatten.';
            header('Location: ?page=register');
            exit;
        }
        if ($password !== $confirm) {
            $_SESSION['register_error'] = 'Wachtwoorden komen niet overeen.';
            header('Location: ?page=register');
            exit;
        }

        try {
            $id = $this->userModel->createPublicUser($name, $email, $password);
            $user = $this->userModel->getById($id);
            session_regenerate_id(true);
            $_SESSION['auth_user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ];
            unset($_SESSION['register_old']);
            header('Location: ?page=home');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['register_error'] = 'Registratie mislukt. Probeer het opnieuw.';
            header('Location: ?page=register');
            exit;
        }
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

        $debugCurlErr  = null;
        $debugHttpCode = null;

        if ($repoUrl !== '') {
            if (!Auth::check()) {
                $redirectTarget = '?page=readmesync&repo=' . urlencode($repoUrl);
                header('Location: ?page=login&redirect=' . urlencode($redirectTarget));
                exit;
            }

            if (!$this->isValidGitHubUrl($repoUrl)) {
                $error = 'Ongeldige GitHub URL. Verwacht: https://github.com/owner/repo';
            } elseif (!function_exists('curl_init')) {
                $error = 'cURL is niet beschikbaar op deze server.';
            } else {
                $payload = json_encode(['githubRepoUrl' => $repoUrl]);
                $curlOpts = [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 35,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
                    CURLOPT_SSL_VERIFYPEER => true,
                ];

                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, $curlOpts);

                $raw      = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr  = curl_error($ch);
                curl_close($ch);

                // SSL fallback: Combell shared hosting may have outdated CA bundle
                if ($curlErr) {
                    $curlErrLower = strtolower($curlErr);
                    if (strpos($curlErrLower, 'ssl') !== false || strpos($curlErrLower, 'certificate') !== false) {
                        $fallbackOpts = $curlOpts;
                        $fallbackOpts[CURLOPT_SSL_VERIFYPEER] = false;
                        $fallbackOpts[CURLOPT_SSL_VERIFYHOST] = 0;
                        $ch2 = curl_init($apiUrl);
                        curl_setopt_array($ch2, $fallbackOpts);
                        $raw2      = curl_exec($ch2);
                        $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        $curlErr2  = curl_error($ch2);
                        curl_close($ch2);
                        if (!$curlErr2) {
                            $raw      = $raw2;
                            $httpCode = $httpCode2;
                            $curlErr  = '';
                        }
                    }
                }

                if ($curlErr) {
                    $error         = 'De ReadmeSync API is momenteel niet bereikbaar.';
                    $debugCurlErr  = htmlspecialchars($curlErr, ENT_QUOTES, 'UTF-8');
                    $debugHttpCode = (int) $httpCode;
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
            'title'        => 'ReadmeSync – Live Code Overview',
            'repoUrl'      => htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8'),
            'result'       => $result,
            'language'     => $language,
            'error'        => $error,
            'debugCurlErr' => $debugCurlErr,
            'debugHttpCode'=> $debugHttpCode,
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
