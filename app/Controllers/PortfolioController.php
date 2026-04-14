<?php
// /app/Controllers/PortfolioController.php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || ((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    // TODO(security): done - Fallback cookie flags for direct controller entry points.
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

require_once __DIR__ . '/../Config/translations.php';
require_once __DIR__ . '/../Config/env.php';
require_once __DIR__ . '/../Models/ContactMessageModel.php';
require_once __DIR__ . '/../Models/ProjectModels.php';
require_once __DIR__ . '/../Models/SkillModel.php';
require_once __DIR__ . '/../Models/GameStatsModel.php';
require_once __DIR__ . '/../Models/NewsModel.php';
require_once __DIR__ . '/../Models/FaqModel.php';
require_once __DIR__ . '/../Models/NewsCommentModel.php';
require_once __DIR__ . '/../Models/SiteSettingModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/UserSkillModel.php';
require_once __DIR__ . '/../Models/ReadmeSyncScanLogModel.php';
require_once __DIR__ . '/../Services/ProjectRoadmapService.php';
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
    private $userSkillModel;
    private $readmeSyncScanLogModel;
    private $projectRoadmapService;
    private $contactRecipient;

    public function __construct() {
        $this->projectModel   = new ProjectModel();
        $this->skillModel     = new SkillModel();
        $this->gameStatsModel = new GameStatsModel();
        $this->newsModel      = new NewsModel();
        $this->faqModel       = new FaqModel();
        $this->contactModel   = new ContactMessageModel();
        $this->commentModel   = new NewsCommentModel();
        $this->userModel      = new UserModel();
        $this->userSkillModel = new UserSkillModel();
        $this->readmeSyncScanLogModel = new ReadmeSyncScanLogModel();
        $this->projectRoadmapService = new ProjectRoadmapService();
        $this->contactRecipient = portfolioEnv('PORTFOLIO_CONTACT_EMAIL', 'tom1dekoning@gmail.com');
    }

    public function showAbout() {
        $data = [
            'title' => trans('nav_about'),
            'name' => 'Tom Dekoning',
            'email' => $this->contactRecipient,
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

        // Batch-load gallery images so the modal carousel can show them too
        $projectIds    = array_column($projects, 'id');
        $galleryByPid  = $this->projectModel->getGalleryImagesForProjects($projectIds);
        foreach ($projects as &$p) {
            $pid = (int) $p['id'];
            if (!empty($galleryByPid[$pid])) {
                foreach ($galleryByPid[$pid] as $path) {
                    if (!in_array($path, $p['images'], true)) {
                        $p['images'][] = $path;
                    }
                }
            }
        }
        unset($p);

        // TODO(i18n): [P3] page titles in showProjects/showProjectRoadmaps/showGames/showNews/showFaq/showContact
        // are hardcoded strings — replace with trans() calls once keys are added to translations.php.
        $data = [
            'title'        => 'Projecten',
            'projects'     => $projects,
            'projectModel' => $this->projectModel,
        ];
        $this->render('projects', $data);
    }

    public function showProjectDetail(): void {
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $id = (int) ($_GET['id'] ?? 0);
        $project = null;

        if ($slug !== '') {
            $project = $this->projectModel->getProjectBySlug($slug);
        } elseif ($id > 0) {
            $project = $this->projectModel->getProjectById($id);
        }

        if (!$project) {
            $this->show404();
            return;
        }

        $authUser = Auth::user();
        $role = (string) ($authUser['role'] ?? 'user');
        $canSyncRoadmap = in_array($role, ['owner', 'admin'], true);
        $syncMessage = null;

        if ($canSyncRoadmap && isset($_GET['sync']) && (int) $_GET['sync'] === 1) {
            $syncResult = $this->projectRoadmapService->syncProjectRoadmap($project, $authUser);
            if (($syncResult['ok'] ?? false) === true) {
                $syncMessage = 'Roadmap gesynchroniseerd: ' . (int) ($syncResult['itemCount'] ?? 0) . ' TODO items gevonden.';
            } else {
                $syncMessage = 'Roadmap sync mislukt: ' . (string) ($syncResult['error'] ?? 'onbekende fout');
            }
        }

        // Roadmap filter from query string
        $roadmapFilter   = trim((string) ($_GET['filter'] ?? ''));
        $filterStatus    = null;
        $filterPriority  = null;
        if ($roadmapFilter === 'open')  $filterStatus   = 'open';
        if ($roadmapFilter === 'done')  $filterStatus   = 'done';
        if ($roadmapFilter === 'high')  $filterPriority = 'high';

        $projectRoadmap = $this->projectRoadmapService->getProjectRoadmap($project, $filterStatus, $filterPriority);

        $this->render('project-detail', [
            'title'          => (string) ($project['title'] ?? 'Project detail'),
            'project'        => $project,
            'projectRoadmap' => $projectRoadmap,
            'tab'            => (string) ($_GET['tab'] ?? 'overview'),
            'canSyncRoadmap' => $canSyncRoadmap,
            'syncMessage'    => $syncMessage,
            'roadmapFilter'  => $roadmapFilter,
        ]);
    }

    public function showProjectRoadmaps(): void {
        $projects      = $this->projectModel->getAllProjects();
        $roadmapsByKey = $this->projectRoadmapService->getAllProjectRoadmaps();
        $projectIds    = array_column($projects, 'id');
        $syncSummary   = $this->projectRoadmapService->getSyncSummary($projectIds);

        $this->render('project-roadmaps', [
            'title'         => 'Project Roadmaps',
            'projects'      => $projects,
            'roadmapsByKey' => $roadmapsByKey,
            'syncSummary'   => $syncSummary,
        ]);
    }

    public function showContact() {
        // TODO(settings): done - contact_form_enabled gate respected; shows disabled-state when setting is off.
        $contactEnabled = (bool) SiteSettingModel::get('contact_form_enabled', true);
        $data = [
            'title'          => 'Contact',
            'success'        => $_SESSION['contact_success'] ?? false,
            'error'          => $_SESSION['contact_error'] ?? false,
            'contactEnabled' => $contactEnabled,
        ];
        unset($_SESSION['contact_success'], $_SESSION['contact_error']);
        $this->render('contact', $data);
    }

    public function handleContact($postData) {
        // Guard: refuse submissions when contact form is disabled via site settings.
        if (!(bool) SiteSettingModel::get('contact_form_enabled', true)) {
            http_response_code(403);
            header('Location: ?page=contact');
            exit;
        }

        // TODO(security): [P2] add rate limiting / honeypot field to prevent contact form spam
        // TODO(input): [P2] sanitizeInput() applies htmlspecialchars() before storage, causing double-encoding
        // TODO(i18n): done - Dutch validation/error strings replaced with trans() keys.
        // when data is displayed later (view would encode again). Use trim() here for storage,
        // htmlspecialchars() only at display time in the view.
        $name = $this->sanitizeInput($postData['name'] ?? '');
        $email = $this->sanitizeInput($postData['email'] ?? '');
        $message = $this->sanitizeInput($postData['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['contact_error'] = trans('form_all_required');
            header('Location: ?page=contact');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contact_error'] = trans('form_email_invalid');
            header('Location: ?page=contact');
            exit;
        }

        if (strlen($name) < 2) {
            $_SESSION['contact_error'] = trans('form_name_short');
            header('Location: ?page=contact');
            exit;
        }

        if (strlen($message) < 10) {
            $_SESSION['contact_error'] = trans('form_message_short');
            header('Location: ?page=contact');
            exit;
        }

        if (!function_exists('mail')) {
            $_SESSION['contact_error'] = trans('form_mail_unavailable') . $this->contactRecipient;
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
            $_SESSION['contact_success'] = trans('contact_success_sent');
        } else {
            $_SESSION['contact_success'] = trans('contact_success_received');
        }

        header('Location: ?page=contact');
        exit;
    }

    public function downloadCV() {
        // TODO(download): Replace the placeholder CV PDF with the real file and verify the download route.
        $file = __DIR__ . '/../../public/files/CV_JouwNaam.pdf';

        // Refuse to serve a placeholder/empty file (< 1 KB is treated as a stub)
        if (!file_exists($file) || filesize($file) < 1024) {
            $_SESSION['flash'] = ['type' => 'info', 'message' => 'The CV file is not available yet. Please check back later.'];
            header('Location: ?page=contact');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="CV_JouwNaam.pdf"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($file);
        exit;
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
        // TODO(news): Verify comment post/refresh flow so new comments are visible immediately after submit.
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

        $skills = [];
        try {
            $skills = $this->userSkillModel->getPublicByUserId((int) $user['id']);
        } catch (\Throwable $e) {
            $skills = [];
        }

        $this->render('profile', [
            'title'       => htmlspecialchars($user['username']) . '\'s profiel',
            'profileUser' => $user,
            'profileSkills' => $skills,
        ]);
    }

    public function showSettings(): void {
        if (!Auth::check()) {
            header('Location: ?page=login&redirect=' . urlencode('?page=settings'));
            exit;
        }

        $authUser = Auth::user();
        $user = $this->userModel->getById((int) $authUser['id']);
        if (!$user) {
            header('Location: ?page=home');
            exit;
        }

        $skills = $this->userSkillModel->getByUserId((int) $authUser['id']);
        $flash = $_SESSION['settings_flash'] ?? null;
        unset($_SESSION['settings_flash']);

        // TODO(profile): done - Reintroduced user settings page (language/public profile + skills management).
        $this->render('settings', [
            'title' => trans('nav_settings'),
            'user' => $user,
            'skills' => $skills,
            'flash' => $flash,
        ]);
    }

    public function handleSettings(array $post): void {
        if (!Auth::check()) {
            header('Location: ?page=login&redirect=' . urlencode('?page=settings'));
            exit;
        }

        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) {
            $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Ongeldig beveiligingstoken.'];
            header('Location: ?page=settings');
            exit;
        }

        // TODO(security): [P2] require current password confirmation before sensitive settings updates (email/password/profile security fields).
        // Rotate CSRF token on every successful settings POST to shrink the replay window.
        Auth::rotateCsrf();


        $authUser = Auth::user();
        $action = (string) ($post['settings_action'] ?? 'profile');

        if ($action === 'add_skill') {
            $name = trim((string) ($post['skill_name'] ?? ''));
            $category = trim((string) ($post['skill_category'] ?? ''));
            $level = (int) ($post['skill_level'] ?? 1);
            $yearsExperience = trim((string) ($post['skill_years_experience'] ?? ''));
            $isPublic = isset($post['skill_is_public']) && $post['skill_is_public'] === '1';

            if ($name === '' || $category === '') {
                $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Skill name en category zijn verplicht.'];
                header('Location: ?page=settings');
                exit;
            }

            $yearsValue = $yearsExperience === '' ? null : max(0, min(60, (int) $yearsExperience));
            $this->userSkillModel->create((int) $authUser['id'], $name, $category, $level, $yearsValue, $isPublic);
            $_SESSION['settings_flash'] = ['type' => 'success', 'message' => 'Skill toegevoegd.'];
            header('Location: ?page=settings');
            exit;
        }

        if ($action === 'delete_skill') {
            $skillId = (int) ($post['skill_id'] ?? 0);
            if ($skillId > 0) {
                $this->userSkillModel->deleteForUser($skillId, (int) $authUser['id']);
            }
            $_SESSION['settings_flash'] = ['type' => 'success', 'message' => 'Skill verwijderd.'];
            header('Location: ?page=settings');
            exit;
        }

        if ($action === 'update_skill') {
            $skillId = (int) ($post['skill_id'] ?? 0);
            $name = trim((string) ($post['skill_name'] ?? ''));
            $category = trim((string) ($post['skill_category'] ?? ''));
            $level = (int) ($post['skill_level'] ?? 1);
            $yearsExperience = trim((string) ($post['skill_years_experience'] ?? ''));
            $isPublic = isset($post['skill_is_public']) && $post['skill_is_public'] === '1';

            if ($skillId <= 0 || $name === '' || $category === '') {
                $_SESSION['settings_flash'] = ['type' => 'error', 'message' => 'Skill update mislukt: ontbrekende velden.'];
                header('Location: ?page=settings');
                exit;
            }

            $yearsValue = $yearsExperience === '' ? null : max(0, min(60, (int) $yearsExperience));
            $this->userSkillModel->updateForUser((int) $skillId, (int) $authUser['id'], $name, $category, $level, $yearsValue, $isPublic);
            $_SESSION['settings_flash'] = ['type' => 'success', 'message' => 'Skill bijgewerkt.'];
            header('Location: ?page=settings');
            exit;
        }

        // TODO(profile): done - Added missing email notification preference in user settings migration.
        // TODO(profile): [P3] add optional timezone setting support (users.timezone) and persist it in updateSettings().
        $preferredLanguage = (string) ($post['preferred_language'] ?? 'nl');
        $publicProfile = isset($post['public_profile']) && $post['public_profile'] === '1';
        $emailNotifications = isset($post['email_notifications']) && $post['email_notifications'] === '1';
        $this->userModel->updateSettings((int) $authUser['id'], $preferredLanguage, $publicProfile, $emailNotifications);

        $_SESSION['auth_user']['preferred_language'] = $preferredLanguage === 'en' ? 'en' : 'nl';
        $_SESSION['settings_flash'] = ['type' => 'success', 'message' => 'Settings opgeslagen.'];
        header('Location: ?page=settings');
        exit;
    }

    public function showLogin(): void {
        if (Auth::check()) {
            header('Location: ?page=home');
            exit;
        }
        // TODO(security): done - Validate login redirect as internal-only URL.
        $requestedRedirect = (string) ($_GET['redirect'] ?? '');
        $redirect = $this->isSafeInternalRedirect($requestedRedirect) ? $requestedRedirect : '';
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
        $redirect = (string) ($_POST['redirect'] ?? '');

        // TODO(security): done - Added basic login throttling for public login attempts.
        if (!$this->canAttemptLogin('public')) {
            $_SESSION['login_error'] = 'Te veel loginpogingen. Probeer over enkele minuten opnieuw.';
            header('Location: ?page=login');
            exit;
        }

        if (Auth::loginByEmail($email, $password)) {
            $this->clearLoginFailures('public');
            $user = Auth::user();
            // Admins/owners go to admin panel, regular users go back or home
            if (in_array($user['role'], ['owner', 'admin'], true)) {
                header('Location: ?page=admin');
            } elseif ($this->isSafeInternalRedirect($redirect)) {
                header('Location: ' . $redirect);
            } else {
                header('Location: ?page=home');
            }
            exit;
        }

        $this->registerLoginFailure('public');

        $_SESSION['login_error'] = trans('auth_invalid_credentials');
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
        // TODO(profile): Ask for preferred language during signup or derive it from the current site language instead of defaulting to Dutch.
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
            $_SESSION['register_error'] = trans('form_email_invalid');
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
            // TODO(auth): [P3] use Auth::makeSessionUser($user) here instead of manually building the
            // session payload — keeps session shape consistent with login() and avoids missing
            // fields (profile_photo_path, preferred_language) that the navbar expects.
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
        $apiUrl  = getenv('READMESYNC_API_URL') ?: 'https://tombomekestudio.com/api/readmesync/generate';
        $repoUrl = isset($_GET['repo']) ? trim($_GET['repo']) : '';

        $result   = null;
        $error    = null;
        $language = null;
        $apiContractVersion = null;
        $responseKeys = [];
        $payloadSourceUserId = null;
        $payloadSourceUserName = null;

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
                $authUser = Auth::user();
                $resolvedUserId = null;
                if (!empty($authUser['id'])) {
                    $resolvedUserId = (string) $authUser['id'];
                } elseif (!empty($authUser['user_id'])) {
                    $resolvedUserId = (string) $authUser['user_id'];
                } elseif (!empty($authUser['username'])) {
                    $resolvedUserId = (string) $authUser['username'];
                }

                $resolvedUserName = null;
                if (!empty($authUser['username'])) {
                    $resolvedUserName = (string) $authUser['username'];
                } elseif (!empty($authUser['name'])) {
                    $resolvedUserName = (string) $authUser['name'];
                } elseif (!empty($authUser['display_name'])) {
                    $resolvedUserName = (string) $authUser['display_name'];
                } elseif (!empty($authUser['email'])) {
                    $resolvedUserName = (string) $authUser['email'];
                }

                $payloadSourceUserId = $resolvedUserId;
                $payloadSourceUserName = $resolvedUserName;

                $payload = json_encode([
                    'githubRepoUrl' => $repoUrl,
                    'clientApp' => 'portfolio',
                    'userId' => $resolvedUserId,
                    'userName' => $resolvedUserName,
                ]);
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

                // Capture debug info for ALL outcomes
                $debugCurlErr  = $curlErr ? htmlspecialchars($curlErr, ENT_QUOTES, 'UTF-8') : null;
                $debugHttpCode = (int) $httpCode;
                $debugRawBody  = $raw ? htmlspecialchars(substr($raw, 0, 800), ENT_QUOTES, 'UTF-8') : null;

                if ($curlErr) {
                    $error = 'De ReadmeSync API is momenteel niet bereikbaar.';
                } elseif ($httpCode === 200) {
                    $data     = json_decode($raw, true);
                    $result   = $data['content']  ?? null;
                    $language = $data['language'] ?? null;
                    $apiContractVersion = $data['apiContractVersion'] ?? null;
                    $responseKeys = is_array($data) ? array_keys($data) : [];
                    $debugHttpCode = null; // no debug needed on success
                } elseif ($httpCode === 404) {
                    $decoded = json_decode((string) $raw, true);
                    $detail  = strtolower((string) ($decoded['detail'] ?? $decoded['error'] ?? $raw ?? ''));
                    if (strpos($detail, 'ssl') !== false || strpos($detail, 'certificate') !== false) {
                        $error = 'ReadmeSync backend SSL-probleem: certificaat verlopen of ongeldig.';
                    } else {
                        $repoPublic = $this->isGitHubRepoPublic($repoUrl);
                        if ($repoPublic === true) {
                            $error = 'Repository is publiek bereikbaar, maar ReadmeSync API kan GitHub niet ophalen (controleer API GitHub token/certificaat).';
                        } else {
                            $error = 'Repository niet gevonden of is privé.';
                        }
                    }
                } elseif ($httpCode === 502 || $httpCode === 503 || $httpCode === 504) {
                    $error = 'ReadmeSync API server is momenteel niet beschikbaar (HTTP ' . $httpCode . '). Probeer het later opnieuw.';
                } else {
                    $decoded = json_decode($raw, true);
                    $detail  = strtolower((string) ($decoded['detail'] ?? $decoded['error'] ?? $raw ?? ''));
                    if (strpos($detail, 'ssl') !== false || strpos($detail, 'certificate') !== false) {
                        $error = 'ReadmeSync backend SSL-probleem: certificaat verlopen of ongeldig.';
                    } else {
                        $error = $decoded['detail'] ?? $decoded['error'] ?? 'Er is een fout opgetreden bij het genereren (HTTP ' . $httpCode . ').';
                    }
                }

                $rawDecoded = json_decode((string) ($raw ?? ''), true);
                if (is_array($rawDecoded) && empty($responseKeys)) {
                    $responseKeys = array_keys($rawDecoded);
                    if ($apiContractVersion === null) {
                        $apiContractVersion = $rawDecoded['apiContractVersion'] ?? null;
                    }
                }

                $this->readmeSyncScanLogModel->log([
                    'user_id' => isset($authUser['id']) ? (int) $authUser['id'] : null,
                    'username' => $authUser['username'] ?? null,
                    'user_role' => $authUser['role'] ?? null,
                    'source_client' => 'portfolio',
                    'source_user_id' => $payloadSourceUserId,
                    'source_user_name' => $payloadSourceUserName,
                    'repo_url' => $repoUrl,
                    'success' => $error === null && $result !== null,
                    'http_code' => isset($httpCode) ? (int) $httpCode : null,
                    'language' => $language,
                    'todo_count' => is_array($rawDecoded['todos'] ?? null) ? count($rawDecoded['todos']) : null,
                    'api_contract_version' => is_string($apiContractVersion) ? $apiContractVersion : null,
                    'response_keys' => !empty($responseKeys) ? implode(',', $responseKeys) : null,
                    'error_message' => $error,
                ]);
            }
        }

        $this->render('readmesync', [
            'title'        => 'ReadmeSync – Live Code Overview',
            'repoUrl'      => htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8'),
            'result'       => $result,
            'language'     => $language,
            'error'        => $error,
            'debugCurlErr'  => $debugCurlErr,
            'debugHttpCode' => $debugHttpCode,
            'debugRawBody'  => $debugRawBody ?? null,
            'debugSourceUserId' => $payloadSourceUserId,
            'debugSourceUserName' => $payloadSourceUserName,
            'debugResponseKeys' => $responseKeys,
            'debugApiContractVersion' => $apiContractVersion,
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

    private function parseGitHubOwnerRepo(string $url): ?array {
        $path = trim((string) (parse_url($url, PHP_URL_PATH) ?? ''), '/');
        $parts = explode('/', $path);
        if (count($parts) < 2) {
            return null;
        }
        return [
            'owner' => $parts[0],
            'repo'  => preg_replace('/\.git$/i', '', $parts[1]),
        ];
    }

    private function isGitHubRepoPublic(string $url): ?bool {
        if (!function_exists('curl_init')) {
            return null;
        }
        $parsed = $this->parseGitHubOwnerRepo($url);
        if (!$parsed) {
            return null;
        }

        $api = 'https://api.github.com/repos/' . rawurlencode($parsed['owner']) . '/' . rawurlencode($parsed['repo']);
        $ch = curl_init($api);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['User-Agent: Tombomeke-Portfolio/1.0', 'Accept: application/vnd.github+json'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return null;
        }
        if ($code === 200) {
            return true;
        }
        if ($code === 404) {
            return false;
        }
        return null;
    }

    private function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private function isSafeInternalRedirect(string $redirect): bool {
        if ($redirect === '' || strlen($redirect) > 512) {
            return false;
        }
        if (preg_match('/[\r\n]/', $redirect)) {
            return false;
        }
        if ($redirect[0] !== '?') {
            return false;
        }
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $redirect)) {
            return false;
        }

        $query = (string) parse_url($redirect, PHP_URL_QUERY);
        parse_str($query, $params);
        return !empty($params['page']);
    }

    private function canAttemptLogin(string $scope): bool {
        $windowSeconds = 300;
        $maxAttempts = 8;
        $now = time();
        $record = $_SESSION['login_attempts'][$scope] ?? null;

        if (!is_array($record) || empty($record['first_at']) || ($now - (int) $record['first_at']) > $windowSeconds) {
            $_SESSION['login_attempts'][$scope] = ['count' => 0, 'first_at' => $now];
            return true;
        }

        return ((int) ($record['count'] ?? 0)) < $maxAttempts;
    }

    private function registerLoginFailure(string $scope): void {
        $now = time();
        $record = $_SESSION['login_attempts'][$scope] ?? ['count' => 0, 'first_at' => $now];
        if (($now - (int) ($record['first_at'] ?? $now)) > 300) {
            $record = ['count' => 0, 'first_at' => $now];
        }
        $record['count'] = (int) ($record['count'] ?? 0) + 1;
        $_SESSION['login_attempts'][$scope] = $record;
    }

    private function clearLoginFailures(string $scope): void {
        if (isset($_SESSION['login_attempts'][$scope])) {
            unset($_SESSION['login_attempts'][$scope]);
        }
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

    // TODO(cron): done - last-run guard queries MAX(created_at) from project_sync_log; refuses if < 60 min ago.
    /**
     * Token-protected cron endpoint for periodic roadmap sync.
     * Call via: GET ?page=cron-sync-roadmaps&token=SECRET
     * Set CRON_SYNC_TOKEN as an environment variable on the server.
     * Returns JSON — not meant for browser viewing.
     */
    public function cronSyncRoadmaps(): void {
        header('Content-Type: application/json');

        $token    = trim((string) ($_GET['token'] ?? ''));
        $envToken = trim((string) portfolioEnv('CRON_SYNC_TOKEN'));

        if ($envToken === '' || $token !== $envToken) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // Rate-limit: refuse if the last successful global sync was less than 60 minutes ago.
        try {
            $db     = \Database::getConnection();
            $lastRun = $db->query(
                "SELECT MAX(created_at) FROM project_sync_log WHERE success = 1"
            )->fetchColumn();

            if ($lastRun && (time() - strtotime((string) $lastRun)) < 3600) {
                http_response_code(429);
                echo json_encode([
                    'ok'     => false,
                    'status' => 'skipped',
                    'reason' => 'too_soon',
                    'last_run' => $lastRun,
                ]);
                exit;
            }
        } catch (\Throwable $e) {
            // DB unavailable — skip the check and let sync proceed
        }

        @set_time_limit(120);

        $projects = $this->projectModel->getAllProjects();
        $synced = 0; $failed = 0; $skipped = 0; $results = [];

        foreach ($projects as $project) {
            $repoUrl = trim((string) ($project['repo_url'] ?? ''));
            if ($repoUrl === '') { $skipped++; continue; }

            $result = $this->projectRoadmapService->syncProjectRoadmap($project);
            if ($result['ok'] ?? false) {
                $synced++;
                $results[] = ['slug' => $project['slug'], 'ok' => true, 'items' => $result['itemCount'] ?? 0];
            } else {
                $failed++;
                $results[] = ['slug' => $project['slug'], 'ok' => false, 'error' => $result['error'] ?? ''];
            }
        }

        echo json_encode([
            'ok'      => true,
            'synced'  => $synced,
            'failed'  => $failed,
            'skipped' => $skipped,
            'results' => $results,
        ]);
        exit;
    }
}
?>
