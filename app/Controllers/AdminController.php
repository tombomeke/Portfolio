<?php
// app/Controllers/AdminController.php

require_once __DIR__ . '/../Auth/Auth.php';
require_once __DIR__ . '/../Config/env.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/NewsModel.php';
require_once __DIR__ . '/../Models/FaqModel.php';
require_once __DIR__ . '/../Models/ProjectModels.php';
require_once __DIR__ . '/../Models/ContactMessageModel.php';
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/SkillModel.php';
require_once __DIR__ . '/../Models/TagModel.php';
require_once __DIR__ . '/../Models/NewsCommentModel.php';
require_once __DIR__ . '/../Models/ActivityLogModel.php';
require_once __DIR__ . '/../Models/SiteSettingModel.php';
require_once __DIR__ . '/../Services/ProjectRoadmapService.php';
require_once __DIR__ . '/../Config/translations.php';

class AdminController {

    private NewsModel           $news;
    private FaqModel            $faq;
    private ProjectModel        $projects;
    private ContactMessageModel $contact;
    private UserModel           $users;
    private SkillModel          $skills;
    private TagModel            $tags;
    private NewsCommentModel    $comments;
    private ActivityLogModel    $activityLog;
    private SiteSettingModel    $settings;
    private ProjectRoadmapService $projectRoadmapService;
    private string              $contactEmail;
    private string              $readmeSyncApiUrl = 'https://tombomekestudio.com/api/readmesync/generate';
    private string              $readmeSyncTelemetryApiUrl = 'https://tombomekestudio.com/api/v1/admin/telemetry';
    private string              $readmeSyncTelemetryExportUrl = 'https://tombomekestudio.com/api/v1/admin/telemetry/export';
    private string              $readmeSyncAdminApiKey = '';

    public function __construct() {
        $this->news        = new NewsModel();
        $this->faq         = new FaqModel();
        $this->projects    = new ProjectModel();
        $this->contact     = new ContactMessageModel();
        $this->users       = new UserModel();
        $this->skills      = new SkillModel();
        $this->tags        = new TagModel();
        $this->comments    = new NewsCommentModel();
        $this->activityLog = new ActivityLogModel();
        $this->settings    = new SiteSettingModel();
        $this->projectRoadmapService = new ProjectRoadmapService();
        $this->contactEmail = portfolioEnv('PORTFOLIO_CONTACT_EMAIL', 'tom1dekoning@gmail.com');

        $readmeSyncApiUrl = portfolioEnv('READMESYNC_API_URL', $this->readmeSyncApiUrl);
        $readmeSyncTelemetryApiUrl = portfolioEnv('READMESYNC_ADMIN_TELEMETRY_URL', $this->readmeSyncTelemetryApiUrl);
        $readmeSyncTelemetryExportUrl = portfolioEnv('READMESYNC_ADMIN_TELEMETRY_EXPORT_URL', $this->readmeSyncTelemetryExportUrl);
        $readmeSyncAdminApiKey = portfolioEnv('READMESYNC_ADMIN_API_KEY', '');

        $this->readmeSyncApiUrl = $readmeSyncApiUrl;
        $this->readmeSyncTelemetryApiUrl = $readmeSyncTelemetryApiUrl;
        $this->readmeSyncTelemetryExportUrl = $readmeSyncTelemetryExportUrl;
        $this->readmeSyncAdminApiKey = $readmeSyncAdminApiKey;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DISPATCH
    // ═══════════════════════════════════════════════════════════════════════

    public function dispatch(string $page): void {
        // First-run setup
        if ($page === 'setup') {
            $this->handleSetup();
            return;
        }

        $section = $_GET['section'] ?? 'dashboard';
        $action  = $_GET['action']  ?? 'index';
        $id      = isset($_GET['id']) ? (int) $_GET['id'] : null;
        $isPost  = $_SERVER['REQUEST_METHOD'] === 'POST';

        // Auth-free routes
        if ($section === 'login') {
            $isPost ? $this->handleLogin($_POST) : $this->showLogin();
            return;
        }
        if ($section === 'logout') {
            Auth::logout();
            header('Location: ?page=admin&section=login');
            exit;
        }

        // TODO(auth): done - Require admin role for all admin sections except login/logout/setup.
        Auth::requireAuth();
        Auth::requireAdmin();

        switch ($section) {
            case 'news':
                $this->routeNews($action, $id, $isPost);
                break;
            case 'faq':
                $this->routeFaq($action, $id, $isPost);
                break;
            case 'projects':
                $this->routeProjects($action, $id, $isPost);
                break;
            case 'contact':
                $this->routeContact($action, $id, $isPost);
                break;
            case 'users':
                Auth::requireOwner();
                $this->routeUsers($action, $id, $isPost);
                break;
            case 'dev-life':
                $this->routeDevLife($action, $id, $isPost);
                break;
            case 'tags':
                $this->routeTags($action, $id, $isPost);
                break;
            case 'comments':
                $this->routeComments($action, $id, $isPost);
                break;
            case 'activity-logs':
                Auth::requireOwner();
                $this->routeActivityLogs($action, $id, $isPost);
                break;
            case 'settings':
                Auth::requireOwner();
                $this->routeSettings($action, $isPost);
                break;
            case 'profile':
                $this->routeProfile($action, $isPost);
                break;
            case 'wip':
                Auth::requireOwner();
                $this->routeWip($isPost);
                break;
            case 'roadmap':
                Auth::requireOwner();
                $this->routeRoadmap($isPost);
                break;
            case 'telemetry':
                Auth::requireOwner();
                $this->routeTelemetry();
                break;
            default:
                $this->showDashboard();
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SETUP (first owner account)
    // ═══════════════════════════════════════════════════════════════════════

    private function handleSetup(): void {
        if ($this->users->count() > 0) {
            header('Location: ?page=admin');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password']      ?? '';
            $confirm  = $_POST['confirm']       ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif (!UserModel::isValidUsername($username)) {
                $error = 'Username: 3–30 characters, letters/numbers/underscore only.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $this->users->create($username, $email, $password, 'owner');
                Auth::login($username, $password);
                $this->flash('success', "Welcome, {$username}! Your owner account has been created.");
                header('Location: ?page=admin');
                exit;
            }
        }

        $this->renderAdmin('setup', compact('error'), 'Setup – Create Owner Account');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // LOGIN / LOGOUT
    // ═══════════════════════════════════════════════════════════════════════

    private function showLogin(): void {
        if (Auth::check()) {
            header('Location: ?page=admin');
            exit;
        }
        $error = $this->popFlash('error');
        $this->renderAdmin('login', compact('error'), 'Admin Login');
    }

    private function handleLogin(array $post): void {
        $username = trim($post['username'] ?? '');
        $password = $post['password']      ?? '';

        // TODO(security): done - Added basic login throttling for admin login attempts.
        if (!$this->canAttemptAdminLogin()) {
            $this->flash('error', 'Te veel loginpogingen. Probeer over enkele minuten opnieuw.');
            header('Location: ?page=admin&section=login');
            exit;
        }

        if (Auth::login($username, $password)) {
            $this->clearAdminLoginFailures();
            if (!Auth::isAdmin()) {
                Auth::logout();
                $this->flash('error', 'Je account heeft geen adminrechten.');
                header('Location: ?page=admin&section=login');
                exit;
            }
            ActivityLogModel::log('login', "User '{$username}' logged in");
            header('Location: ?page=admin');
            exit;
        }

        $this->registerAdminLoginFailure();

        $this->flash('error', 'Invalid username or password.');
        header('Location: ?page=admin&section=login');
        exit;
    }

    private function canAttemptAdminLogin(): bool {
        $windowSeconds = 300;
        $maxAttempts = 8;
        $now = time();
        $record = $_SESSION['admin_login_attempts'] ?? null;

        if (!is_array($record) || empty($record['first_at']) || ($now - (int) $record['first_at']) > $windowSeconds) {
            $_SESSION['admin_login_attempts'] = ['count' => 0, 'first_at' => $now];
            return true;
        }

        return ((int) ($record['count'] ?? 0)) < $maxAttempts;
    }

    private function registerAdminLoginFailure(): void {
        $now = time();
        $record = $_SESSION['admin_login_attempts'] ?? ['count' => 0, 'first_at' => $now];
        if (($now - (int) ($record['first_at'] ?? $now)) > 300) {
            $record = ['count' => 0, 'first_at' => $now];
        }
        $record['count'] = (int) ($record['count'] ?? 0) + 1;
        $_SESSION['admin_login_attempts'] = $record;
    }

    private function clearAdminLoginFailures(): void {
        if (isset($_SESSION['admin_login_attempts'])) {
            unset($_SESSION['admin_login_attempts']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════

    private function showDashboard(): void {
        try {
            $stats = [
                'news'             => $this->news->countAll(),
                'faq_categories'   => $this->faq->countCategories(),
                'faq_items'        => $this->faq->countItems(),
                'projects'         => $this->projects->count(),
                'messages'         => $this->contact->count(),
                'unread_messages'  => $this->contact->countUnread(),
                'admin_users'      => $this->users->countAdminUsers(),
                'skills'           => $this->skills->countSkills(),
                'education'        => $this->skills->countEducation(),
                'goals'            => $this->skills->countGoals(),
                'tags'             => $this->tags->count(),
                'pending_comments' => $this->comments->countPending(),
            ];
            $recentNews     = $this->news->getAllForAdmin(5);
            $recentMessages = $this->contact->getAll(5);
            $roadmapConfig  = $this->loadRoadmapConfig();
            $roadmapItems   = $roadmapConfig['items'] ?? [];
            $todoCount      = 0;
            $doneCount      = 0;
            foreach ($roadmapItems as $item) {
                if (($item['status'] ?? 'todo') === 'done') {
                    $doneCount++;
                } else {
                    $todoCount++;
                }
            }
            $roadmapMeta = [
                'source'      => $roadmapConfig['source'] ?? 'manual',
                'repoUrl'     => $roadmapConfig['repoUrl'] ?? '',
                'lastSyncAt'  => $roadmapConfig['lastSyncAt'] ?? null,
                'todoCount'   => $todoCount,
                'doneCount'   => $doneCount,
            ];
        } catch (\Throwable $e) {
            $stats = array_fill_keys(
                ['news','faq_categories','faq_items','projects','messages','unread_messages','admin_users','skills','education','goals','tags','pending_comments'],
                0
            );
            $recentNews = $recentMessages = [];
            $roadmapItems = $this->getDefaultRoadmapItems();
            $roadmapMeta = [
                'source'      => 'manual',
                'repoUrl'     => '',
                'lastSyncAt'  => null,
                'todoCount'   => 0,
                'doneCount'   => count($roadmapItems),
            ];
        }
        $flash = $this->popFlash();
        $this->renderAdmin('dashboard', compact('stats', 'recentNews', 'recentMessages', 'flash', 'roadmapItems', 'roadmapMeta'), 'Dashboard');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // NEWS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeNews(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'create':
                $isPost ? $this->storeNews($_POST, $_FILES) : $this->createNews();
                break;
            case 'edit':
                $isPost ? $this->updateNews($id, $_POST, $_FILES) : $this->editNews($id);
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteNews($id);
                } else {
                    header('Location: ?page=admin&section=news'); exit;
                }
                break;
            default:
                $this->listNews();
        }
    }

    private function listNews(): void {
        $items = $this->news->getAllForAdmin();
        $flash = $this->popFlash();
        $this->renderAdmin('news/index', compact('items', 'flash'), 'News beheren');
    }

    private function createNews(): void {
        $flash   = $this->popFlash();
        $allTags = $this->tags->getAll();
        $this->renderAdmin('news/create', compact('flash', 'allTags'), 'Nieuwsbericht toevoegen');
    }

    private function storeNews(array $post, array $files): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) {
            $this->flash('error', 'Invalid CSRF token.');
            header('Location: ?page=admin&section=news&action=create');
            exit;
        }

        $errors = $this->validateNewsPost($post);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            header('Location: ?page=admin&section=news&action=create');
            exit;
        }

        $imagePath = $this->handleImageUpload($files['image'] ?? null, 'news');

        $publishAction = $this->resolveNewsPublishAction($post);
        $publishedAt = $this->resolveNewsPublishedAt($post, $publishAction);

        $id = $this->news->create([
            'image_path'   => $imagePath,
            'published_at' => $publishedAt,
            'title_nl'     => trim($post['title_nl']),
            'title_en'     => trim($post['title_en']),
            'content_nl'   => trim($post['content_nl']),
            'content_en'   => trim($post['content_en']),
        ]);

        $tagIds = array_filter(array_map('intval', (array)($post['tags'] ?? [])));
        $this->tags->syncForNewsItem($id, $tagIds);

        ActivityLogModel::log('created', "Created news: " . trim($post['title_nl']), 'news_items', $id);
        if ($publishAction === 'save_draft') {
            $this->flash('success', 'Nieuwsbericht opgeslagen als concept.');
        } else {
            $this->flash('success', 'Nieuwsbericht gepubliceerd.');
        }
        header('Location: ?page=admin&section=news');
        exit;
    }

    private function editNews(?int $id): void {
        $item = $id ? $this->news->getByIdForAdmin($id) : null;
        if (!$item) { $this->notFound(); return; }
        $flash       = $this->popFlash();
        $allTags     = $this->tags->getAll();
        $currentTags = $this->tags->getForNewsItem($id);
        $currentTagIds = array_column($currentTags, 'id');
        $this->renderAdmin('news/edit', compact('item', 'flash', 'allTags', 'currentTagIds'), 'Nieuwsbericht bewerken');
    }

    private function updateNews(?int $id, array $post, array $files): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) {
            $this->flash('error', 'Invalid CSRF token.');
            header("Location: ?page=admin&section=news&action=edit&id={$id}");
            exit;
        }

        $item = $id ? $this->news->getByIdForAdmin($id) : null;
        if (!$item) { $this->notFound(); return; }

        $errors = $this->validateNewsPost($post);
        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            header("Location: ?page=admin&section=news&action=edit&id={$id}");
            exit;
        }

        $imagePath = $item['image_path'];
        if (!empty($post['remove_image'])) {
            if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
                unlink(__DIR__ . '/../../' . $imagePath);
            }
            $imagePath = null;
        }
        $newImage = $this->handleImageUpload($files['image'] ?? null, 'news');
        if ($newImage) $imagePath = $newImage;

        $publishAction = $this->resolveNewsPublishAction($post);
        $publishedAt = $this->resolveNewsPublishedAt($post, $publishAction);

        $this->news->update($id, [
            'image_path'   => $imagePath,
            'published_at' => $publishedAt,
            'title_nl'     => trim($post['title_nl']),
            'title_en'     => trim($post['title_en']),
            'content_nl'   => trim($post['content_nl']),
            'content_en'   => trim($post['content_en']),
        ]);

        $tagIds = array_filter(array_map('intval', (array)($post['tags'] ?? [])));
        $this->tags->syncForNewsItem($id, $tagIds);

        ActivityLogModel::log('updated', "Updated news: " . trim($post['title_nl']), 'news_items', $id);
        if ($publishAction === 'save_draft') {
            $this->flash('success', 'Nieuwsbericht bijgewerkt en als concept opgeslagen.');
        } else {
            $this->flash('success', 'Nieuwsbericht bijgewerkt en gepubliceerd.');
        }
        header('Location: ?page=admin&section=news');
        exit;
    }

    private function resolveNewsPublishAction(array $post): string {
        $action = strtolower(trim((string) ($post['publish_action'] ?? 'publish_now')));
        return $action === 'save_draft' ? 'save_draft' : 'publish_now';
    }

    private function resolveNewsPublishedAt(array $post, string $publishAction): ?string {
        if ($publishAction === 'save_draft') {
            return null;
        }

        $scheduledRaw = trim((string) ($post['published_at'] ?? ''));
        if ($scheduledRaw !== '') {
            $ts = strtotime($scheduledRaw);
            if ($ts !== false) {
                return date('Y-m-d H:i:s', $ts);
            }
        }

        // Explicit publish defaults to server-side current timestamp.
        return date('Y-m-d H:i:s');
    }

    private function deleteNews(?int $id): void {
        if ($id) {
            $item = $this->news->getByIdForAdmin($id);
            $this->news->delete($id);
            ActivityLogModel::log('deleted', "Deleted news: " . ($item['title_nl'] ?? $id), 'news_items', $id);
        }
        $this->flash('success', 'Nieuwsbericht verwijderd.');
        header('Location: ?page=admin&section=news');
        exit;
    }

    private function validateNewsPost(array $post): array {
        $errors = [];
        if (empty(trim($post['title_nl'] ?? ''))) $errors[] = 'Titel (NL) is verplicht.';
        if (empty(trim($post['title_en'] ?? ''))) $errors[] = 'Titel (EN) is verplicht.';
        if (empty(trim($post['content_nl'] ?? ''))) $errors[] = 'Inhoud (NL) is verplicht.';
        if (empty(trim($post['content_en'] ?? ''))) $errors[] = 'Inhoud (EN) is verplicht.';
        return $errors;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // FAQ
    // ═══════════════════════════════════════════════════════════════════════

    private function routeFaq(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'category-create':
                $isPost ? $this->storeFaqCategory($_POST) : $this->createFaqCategory();
                break;
            case 'category-edit':
                $isPost ? $this->updateFaqCategory($id, $_POST) : $this->editFaqCategory($id);
                break;
            case 'category-delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteFaqCategory($id);
                } else {
                    header('Location: ?page=admin&section=faq'); exit;
                }
                break;
            case 'item-create':
                $isPost ? $this->storeFaqItem($_POST) : $this->createFaqItem((int) ($_GET['cat'] ?? 0));
                break;
            case 'item-edit':
                $isPost ? $this->updateFaqItem($id, $_POST) : $this->editFaqItem($id);
                break;
            case 'item-delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteFaqItem($id);
                } else {
                    header('Location: ?page=admin&section=faq'); exit;
                }
                break;
            default:
                $this->listFaq();
        }
    }

    private function listFaq(): void {
        $categories = $this->faq->getAllCategoriesForAdmin();
        $flash      = $this->popFlash();
        $this->renderAdmin('faq/index', compact('categories', 'flash'), 'FAQ beheren');
    }

    private function createFaqCategory(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('faq/category-create', compact('flash'), 'Categorie toevoegen');
    }

    private function storeFaqCategory(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=faq&action=category-create'); return; }
        $errors = [];
        if (empty(trim($post['slug']    ?? ''))) $errors[] = 'Slug is verplicht.';
        if (empty(trim($post['name_nl'] ?? ''))) $errors[] = 'Naam (NL) is verplicht.';
        if (empty(trim($post['name_en'] ?? ''))) $errors[] = 'Naam (EN) is verplicht.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header('Location: ?page=admin&section=faq&action=category-create'); exit; }

        $this->faq->createCategory([
            'slug'       => trim($post['slug']),
            'sort_order' => (int) ($post['sort_order'] ?? 0),
            'name_nl'    => trim($post['name_nl']),
            'name_en'    => trim($post['name_en']),
        ]);
        $this->flash('success', 'Categorie aangemaakt.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    private function editFaqCategory(?int $id): void {
        $category = $id ? $this->faq->getCategoryByIdForAdmin($id) : null;
        if (!$category) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('faq/category-edit', compact('category', 'flash'), 'Categorie bewerken');
    }

    private function updateFaqCategory(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=faq&action=category-edit&id={$id}"); return; }
        $errors = [];
        if (empty(trim($post['slug']    ?? ''))) $errors[] = 'Slug is verplicht.';
        if (empty(trim($post['name_nl'] ?? ''))) $errors[] = 'Naam (NL) is verplicht.';
        if (empty(trim($post['name_en'] ?? ''))) $errors[] = 'Naam (EN) is verplicht.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header("Location: ?page=admin&section=faq&action=category-edit&id={$id}"); exit; }

        $this->faq->updateCategory($id, [
            'slug'       => trim($post['slug']),
            'sort_order' => (int) ($post['sort_order'] ?? 0),
            'name_nl'    => trim($post['name_nl']),
            'name_en'    => trim($post['name_en']),
        ]);
        $this->flash('success', 'Categorie bijgewerkt.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    private function deleteFaqCategory(?int $id): void {
        if ($id) $this->faq->deleteCategory($id);
        $this->flash('success', 'Categorie verwijderd.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    private function createFaqItem(int $catId): void {
        $categories = $this->faq->getAllCategoriesForAdmin();
        $flash      = $this->popFlash();
        $this->renderAdmin('faq/item-create', compact('categories', 'catId', 'flash'), 'FAQ-item toevoegen');
    }

    private function storeFaqItem(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=faq&action=item-create'); return; }
        $errors = [];
        if (empty($post['faq_category_id'])) $errors[] = 'Categorie is verplicht.';
        if (empty(trim($post['question_nl'] ?? ''))) $errors[] = 'Vraag (NL) is verplicht.';
        if (empty(trim($post['question_en'] ?? ''))) $errors[] = 'Vraag (EN) is verplicht.';
        if (empty(trim($post['answer_nl']   ?? ''))) $errors[] = 'Antwoord (NL) is verplicht.';
        if (empty(trim($post['answer_en']   ?? ''))) $errors[] = 'Antwoord (EN) is verplicht.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header('Location: ?page=admin&section=faq&action=item-create'); exit; }

        $this->faq->createItem([
            'faq_category_id' => (int) $post['faq_category_id'],
            'sort_order'      => (int) ($post['sort_order'] ?? 0),
            'question_nl'     => trim($post['question_nl']),
            'question_en'     => trim($post['question_en']),
            'answer_nl'       => trim($post['answer_nl']),
            'answer_en'       => trim($post['answer_en']),
        ]);
        $this->flash('success', 'FAQ-item aangemaakt.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    private function editFaqItem(?int $id): void {
        $item       = $id ? $this->faq->getItemByIdForAdmin($id) : null;
        if (!$item) { $this->notFound(); return; }
        $categories = $this->faq->getAllCategoriesForAdmin();
        $flash      = $this->popFlash();
        $this->renderAdmin('faq/item-edit', compact('item', 'categories', 'flash'), 'FAQ-item bewerken');
    }

    private function updateFaqItem(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=faq&action=item-edit&id={$id}"); return; }
        $this->faq->updateItem($id, [
            'faq_category_id' => (int) ($post['faq_category_id'] ?? 0),
            'sort_order'      => (int) ($post['sort_order'] ?? 0),
            'question_nl'     => trim($post['question_nl'] ?? ''),
            'question_en'     => trim($post['question_en'] ?? ''),
            'answer_nl'       => trim($post['answer_nl']   ?? ''),
            'answer_en'       => trim($post['answer_en']   ?? ''),
        ]);
        $this->flash('success', 'FAQ-item bijgewerkt.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    private function deleteFaqItem(?int $id): void {
        if ($id) $this->faq->deleteItem($id);
        $this->flash('success', 'FAQ-item verwijderd.');
        header('Location: ?page=admin&section=faq');
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PROJECTS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeProjects(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'create':
                $isPost ? $this->storeProject($_POST, $_FILES) : $this->createProject();
                break;
            case 'edit':
                $isPost ? $this->updateProject($id, $_POST, $_FILES) : $this->editProject($id);
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteProject($id);
                } else {
                    header('Location: ?page=admin&section=projects'); exit;
                }
                break;
            case 'sync-all':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->syncAllProjectRoadmaps();
                } else {
                    header('Location: ?page=admin&section=projects'); exit;
                }
                break;
            default:
                $this->listProjects();
        }
    }

    // TODO(admin): [P3] show per-project sync result detail on sync-all completion page
    private function syncAllProjectRoadmaps(): void {
        // Simple rate limit: check last sync-all via session
        $lastSyncAll = (int) ($_SESSION['last_sync_all'] ?? 0);
        if (time() - $lastSyncAll < 300) {
            $this->flash('error', 'Sync all is al minder dan 5 minuten geleden uitgevoerd. Wacht even.');
            header('Location: ?page=admin&section=projects'); exit;
        }

        $projects = $this->projects->getAllForAdmin();
        $synced = 0; $failed = 0; $skipped = 0;
        $authUser = Auth::user();

        @set_time_limit(120);

        foreach ($projects as $project) {
            $repoUrl = trim((string) ($project['repo_url'] ?? ''));
            if ($repoUrl === '') { $skipped++; continue; }

            $result = $this->projectRoadmapService->syncProjectRoadmap([
                'id'       => (int) $project['id'],
                'slug'     => (string) $project['slug'],
                'title'    => (string) ($project['title_nl'] ?? ''),
                'repo_url' => $repoUrl,
            ], $authUser);

            if ($result['ok'] ?? false) { $synced++; } else { $failed++; }
        }

        $_SESSION['last_sync_all'] = time();
        $this->flash('success', "Sync all klaar: {$synced} geslaagd, {$failed} mislukt, {$skipped} overgeslagen (geen repo URL).");
        header('Location: ?page=admin&section=projects'); exit;
    }

    private function listProjects(): void {
        $projects  = $this->projects->getAllForAdmin();
        $syncLogs  = $this->projectRoadmapService->getRecentSyncLogs(30);
        $flash     = $this->popFlash();
        $this->renderAdmin('projects/index', compact('projects', 'syncLogs', 'flash'), 'Projecten beheren');
    }

    private function createProject(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('projects/create', compact('flash'), 'Project toevoegen');
    }

    private function storeProject(array $post, array $files): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=projects&action=create'); return; }

        $errors = $this->validateProjectPost($post);
        if ($errors) { $this->flash('error', implode(' ', $errors)); header('Location: ?page=admin&section=projects&action=create'); exit; }

        $imagePath = $this->handleImageUpload($files['image'] ?? null, 'projects');
        $tech = $this->parseTech($post['tech'] ?? '');

        $projectId = $this->projects->create([
            'slug'               => trim($post['slug']),
            'category'           => trim($post['category']),
            'status'             => trim($post['status'] ?? ''),
            'image_path'         => $imagePath,
            'repo_url'           => trim($post['repo_url'] ?? ''),
            'demo_url'           => trim($post['demo_url'] ?? ''),
            'tech'               => $tech,
            'sort_order'         => (int) ($post['sort_order'] ?? 0),
            'title_nl'           => trim($post['title_nl']),
            'title_en'           => trim($post['title_en']),
            'description_nl'     => trim($post['description_nl']),
            'description_en'     => trim($post['description_en']),
            'long_description_nl' => trim($post['long_description_nl'] ?? ''),
            'long_description_en' => trim($post['long_description_en'] ?? ''),
            'features_nl'        => $this->parseFeatures($post['features_nl'] ?? ''),
            'features_en'        => $this->parseFeatures($post['features_en'] ?? ''),
        ]);

        // Handle additional gallery images
        if (!empty($files['gallery_images']['name'][0])) {
            $galleryPaths = $this->handleMultiImageUpload($files['gallery_images'], 'projects');
            foreach ($galleryPaths as $i => $path) {
                $this->projects->addImage($projectId, $path, $i);
            }
        }

        $projectForSync = [
            'id' => $projectId,
            'slug' => trim($post['slug']),
            'title' => trim($post['title_nl'] ?? ''),
            'repo_url' => trim($post['repo_url'] ?? ''),
        ];
        $syncNotice = $this->syncProjectRoadmapIfPossible($projectForSync);

        $this->flash('success', 'Project aangemaakt.' . ($syncNotice ? ' ' . $syncNotice : ''));
        header('Location: ?page=admin&section=projects');
        exit;
    }

    private function editProject(?int $id): void {
        $project = $id ? $this->projects->getByIdForAdmin($id) : null;
        if (!$project) { $this->notFound(); return; }
        $galleryImages = $id ? $this->projects->getImagesByProjectId($id) : [];
        $flash = $this->popFlash();
        $this->renderAdmin('projects/edit', compact('project', 'galleryImages', 'flash'), 'Project bewerken');
    }

    private function updateProject(?int $id, array $post, array $files): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=projects&action=edit&id={$id}"); return; }

        $project = $id ? $this->projects->getByIdForAdmin($id) : null;
        if (!$project) { $this->notFound(); return; }

        $errors = $this->validateProjectPost($post);
        if ($errors) { $this->flash('error', implode(' ', $errors)); header("Location: ?page=admin&section=projects&action=edit&id={$id}"); exit; }

        $imagePath = $project['image_path'];
        if (!empty($post['remove_image']) && $imagePath) {
            $full = __DIR__ . '/../../' . $imagePath;
            if (file_exists($full)) unlink($full);
            $imagePath = null;
        }
        $newImage = $this->handleImageUpload($files['image'] ?? null, 'projects');
        if ($newImage) $imagePath = $newImage;

        $this->projects->update($id, [
            'slug'               => trim($post['slug']),
            'category'           => trim($post['category']),
            'status'             => trim($post['status'] ?? ''),
            'image_path'         => $imagePath,
            'repo_url'           => trim($post['repo_url'] ?? ''),
            'demo_url'           => trim($post['demo_url'] ?? ''),
            'tech'               => $this->parseTech($post['tech'] ?? ''),
            'sort_order'         => (int) ($post['sort_order'] ?? 0),
            'title_nl'           => trim($post['title_nl']),
            'title_en'           => trim($post['title_en']),
            'description_nl'     => trim($post['description_nl']),
            'description_en'     => trim($post['description_en']),
            'long_description_nl' => trim($post['long_description_nl'] ?? ''),
            'long_description_en' => trim($post['long_description_en'] ?? ''),
            'features_nl'        => $this->parseFeatures($post['features_nl'] ?? ''),
            'features_en'        => $this->parseFeatures($post['features_en'] ?? ''),
        ]);

        // Delete selected gallery images
        $deleteImageIds = array_map('intval', (array) ($post['delete_images'] ?? []));
        foreach ($deleteImageIds as $imageId) {
            if ($imageId > 0) $this->projects->deleteImage($imageId);
        }

        // Handle new gallery images
        if (!empty($files['gallery_images']['name'][0])) {
            $galleryPaths = $this->handleMultiImageUpload($files['gallery_images'], 'projects');
            foreach ($galleryPaths as $i => $path) {
                $this->projects->addImage((int) $id, $path, $i);
            }
        }

        $projectForSync = [
            'id' => (int) $id,
            'slug' => trim($post['slug']),
            'title' => trim($post['title_nl'] ?? ''),
            'repo_url' => trim($post['repo_url'] ?? ''),
        ];
        $syncNotice = $this->syncProjectRoadmapIfPossible($projectForSync);

        $this->flash('success', 'Project bijgewerkt.' . ($syncNotice ? ' ' . $syncNotice : ''));
        header('Location: ?page=admin&section=projects');
        exit;
    }

    private function deleteProject(?int $id): void {
        if ($id) $this->projects->delete($id);
        $this->flash('success', 'Project verwijderd.');
        header('Location: ?page=admin&section=projects');
        exit;
    }

    private function validateProjectPost(array $post): array {
        $errors = [];
        if (empty(trim($post['slug']           ?? ''))) $errors[] = 'Slug is verplicht.';
        if (empty(trim($post['category']       ?? ''))) $errors[] = 'Categorie is verplicht.';
        if (empty(trim($post['title_nl']       ?? ''))) $errors[] = 'Titel (NL) is verplicht.';
        if (empty(trim($post['title_en']       ?? ''))) $errors[] = 'Titel (EN) is verplicht.';
        if (empty(trim($post['description_nl'] ?? ''))) $errors[] = 'Beschrijving (NL) is verplicht.';
        if (empty(trim($post['description_en'] ?? ''))) $errors[] = 'Beschrijving (EN) is verplicht.';
        return $errors;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // CONTACT
    // ═══════════════════════════════════════════════════════════════════════

    private function routeContact(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'show':
                $this->showContact($id);
                break;
            case 'reply':
                $this->replyContact($id, $_POST);
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteContact($id);
                } else {
                    header('Location: ?page=admin&section=contact'); exit;
                }
                break;
            default:
                $this->listContact();
        }
    }

    private function listContact(): void {
        $messages = $this->contact->getAll();
        $flash    = $this->popFlash();
        $this->renderAdmin('contact/index', compact('messages', 'flash'), 'Contact berichten');
    }

    private function showContact(?int $id): void {
        $message = $id ? $this->contact->getById($id) : null;
        if (!$message) { $this->notFound(); return; }
        $this->contact->markRead($id);
        $flash = $this->popFlash();
        $this->renderAdmin('contact/show', compact('message', 'flash'), 'Bericht bekijken');
    }

    private function replyContact(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=contact&action=show&id={$id}"); return; }

        $message = $id ? $this->contact->getById($id) : null;
        if (!$message) { $this->notFound(); return; }

        $reply = trim($post['reply'] ?? '');
        if (empty($reply)) {
            $this->flash('error', 'Antwoord mag niet leeg zijn.');
            header("Location: ?page=admin&section=contact&action=show&id={$id}");
            exit;
        }

        // Send email reply
        $to      = $message['email'];
        $subject = 'Re: ' . ($message['subject'] ?: 'Uw bericht via tombomeke.com');
        $body    = "Hallo {$message['name']},\n\n{$reply}\n\n---\nTom Dekoning\ntombomeke.com";
        $headers = implode("\r\n", [
            "From: Tom Dekoning <{$this->contactEmail}>",
            "Reply-To: {$this->contactEmail}",
            "MIME-Version: 1.0",
            "Content-Type: text/plain; charset=UTF-8",
        ]);
        mail($to, $subject, $body, $headers);

        $this->contact->saveReply($id, $reply);
        $this->flash('success', 'Antwoord verstuurd en opgeslagen.');
        header("Location: ?page=admin&section=contact&action=show&id={$id}");
        exit;
    }

    private function deleteContact(?int $id): void {
        if ($id) $this->contact->delete($id);
        $this->flash('success', 'Bericht verwijderd.');
        header('Location: ?page=admin&section=contact');
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // USERS (owner only)
    // ═══════════════════════════════════════════════════════════════════════

    private function routeUsers(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'create':
                $isPost ? $this->storeUser($_POST) : $this->createUser();
                break;
            case 'promote':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->promoteUser($id);
                } else {
                    header('Location: ?page=admin&section=users'); exit;
                }
                break;
            case 'demote':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->demoteUser($id);
                } else {
                    header('Location: ?page=admin&section=users'); exit;
                }
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteUser($id);
                } else {
                    header('Location: ?page=admin&section=users'); exit;
                }
                break;
            default:
                $this->listUsers();
        }
    }

    private function listUsers(): void {
        $userList = $this->users->getAll();
        $flash    = $this->popFlash();
        $this->renderAdmin('users/index', compact('userList', 'flash'), 'Gebruikers beheren');
    }

    private function createUser(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('users/create', compact('flash'), 'Admin toevoegen');
    }

    private function storeUser(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=users&action=create'); return; }

        $username = trim($post['username'] ?? '');
        $email    = trim($post['email']    ?? '');
        $password = $post['password']      ?? '';
        $confirm  = $post['confirm']       ?? '';
        $role     = ($post['role'] ?? 'admin') === 'admin' ? 'admin' : 'admin'; // owners cannot be created here

        $errors = [];
        if (!UserModel::isValidUsername($username)) $errors[] = 'Ongeldige gebruikersnaam (3–30 tekens, letters/cijfers/_).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ongeldig e-mailadres.';
        if (strlen($password) < 8) $errors[] = 'Wachtwoord minimaal 8 tekens.';
        if ($password !== $confirm) $errors[] = 'Wachtwoorden komen niet overeen.';
        if ($this->users->usernameExists($username)) $errors[] = 'Gebruikersnaam al in gebruik.';
        if ($this->users->emailExists($email)) $errors[] = 'E-mailadres al in gebruik.';

        if ($errors) {
            $this->flash('error', implode(' ', $errors));
            header('Location: ?page=admin&section=users&action=create');
            exit;
        }

        $this->users->create($username, $email, $password, 'admin');
        $this->flash('success', "Admin-account '{$username}' aangemaakt.");
        header('Location: ?page=admin&section=users');
        exit;
    }

    private function promoteUser(?int $id): void {
        if (!$id) { header('Location: ?page=admin&section=users'); exit; }

        $target = $this->users->getById($id);
        if (!$target) {
            $this->flash('error', 'Gebruiker niet gevonden.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        if ($target['role'] !== 'user') {
            $this->flash('error', 'Alleen normale users kunnen gepromoveerd worden.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        $this->users->updateRole((int) $id, 'admin');
        $this->flash('success', "Gebruiker '{$target['username']}' is nu admin.");
        header('Location: ?page=admin&section=users');
        exit;
    }

    private function demoteUser(?int $id): void {
        if (!$id) { header('Location: ?page=admin&section=users'); exit; }

        $currentUser = Auth::user();
        if ($currentUser && (int) $id === (int) ($currentUser['id'] ?? 0)) {
            $this->flash('error', 'Je kunt je eigen account niet degraderen.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        $target = $this->users->getById($id);
        if (!$target) {
            $this->flash('error', 'Gebruiker niet gevonden.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        if ($target['role'] !== 'admin') {
            $this->flash('error', 'Alleen admins kunnen gedegradeerd worden.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        $this->users->updateRole((int) $id, 'user');
        $this->flash('success', "Admin '{$target['username']}' is nu user.");
        header('Location: ?page=admin&section=users');
        exit;
    }

    private function deleteUser(?int $id): void {
        if (!$id) { header('Location: ?page=admin&section=users'); exit; }

        $currentUser = Auth::user();
        if ($id === $currentUser['id']) {
            $this->flash('error', 'Je kunt je eigen account niet verwijderen.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        $target = $this->users->getById($id);
        if ($target && $target['role'] === 'owner') {
            $this->flash('error', 'Owner-accounts kunnen niet worden verwijderd.');
            header('Location: ?page=admin&section=users');
            exit;
        }

        $this->users->delete($id);
        $this->flash('success', 'Gebruiker verwijderd.');
        header('Location: ?page=admin&section=users');
        exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // DEV LIFE (skills, education, learning goals)
    // ═══════════════════════════════════════════════════════════════════════

    private function routeDevLife(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            // Skills
            case 'skill-create':
                $isPost ? $this->storeSkill($_POST) : $this->createSkill();
                break;
            case 'skill-edit':
                $isPost ? $this->updateSkill($id, $_POST) : $this->editSkill($id);
                break;
            case 'skill-delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteSkill($id);
                } else { header('Location: ?page=admin&section=dev-life'); exit; }
                break;
            // Education
            case 'edu-create':
                $isPost ? $this->storeEducation($_POST) : $this->createEducation();
                break;
            case 'edu-edit':
                $isPost ? $this->updateEducation($id, $_POST) : $this->editEducation($id);
                break;
            case 'edu-delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteEducation($id);
                } else { header('Location: ?page=admin&section=dev-life'); exit; }
                break;
            // Learning goals
            case 'goal-create':
                $isPost ? $this->storeGoal($_POST) : $this->createGoal();
                break;
            case 'goal-edit':
                $isPost ? $this->updateGoal($id, $_POST) : $this->editGoal($id);
                break;
            case 'goal-delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                    $this->deleteGoal($id);
                } else { header('Location: ?page=admin&section=dev-life'); exit; }
                break;
            default:
                $this->listDevLife();
        }
    }

    private function listDevLife(): void {
        $skillList  = $this->skills->getAllSkillsForAdmin();
        $education  = $this->skills->getAllEducationForAdmin();
        $goals      = $this->skills->getAllGoalsForAdmin();
        $flash      = $this->popFlash();
        $this->renderAdmin('dev-life/index', compact('skillList', 'education', 'goals', 'flash'), 'Dev Life beheren');
    }

    private function createSkill(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/skill-create', compact('flash'), 'Skill toevoegen');
    }

    private function storeSkill(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=dev-life&action=skill-create'); return; }
        $errors = [];
        if (empty(trim($post['name']     ?? ''))) $errors[] = 'Naam is verplicht.';
        if (empty(trim($post['category'] ?? ''))) $errors[] = 'Categorie is verplicht.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header('Location: ?page=admin&section=dev-life&action=skill-create'); exit; }

        $projects = array_values(array_filter(array_map('trim', explode("\n", $post['projects'] ?? ''))));
        $this->skills->createSkill([
            'name'       => trim($post['name']),
            'category'   => trim($post['category']),
            'level'      => (int) ($post['level'] ?? 1),
            'notes'      => trim($post['notes'] ?? ''),
            'projects'   => $projects,
            'sort_order' => (int) ($post['sort_order'] ?? 0),
        ]);
        $this->flash('success', 'Skill aangemaakt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function editSkill(?int $id): void {
        $skill = $id ? $this->skills->getSkillByIdForAdmin($id) : null;
        if (!$skill) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/skill-edit', compact('skill', 'flash'), 'Skill bewerken');
    }

    private function updateSkill(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=dev-life&action=skill-edit&id={$id}"); return; }
        $projects = array_values(array_filter(array_map('trim', explode("\n", $post['projects'] ?? ''))));
        $this->skills->updateSkill($id, [
            'name'       => trim($post['name']),
            'category'   => trim($post['category']),
            'level'      => (int) ($post['level'] ?? 1),
            'notes'      => trim($post['notes'] ?? ''),
            'projects'   => $projects,
            'sort_order' => (int) ($post['sort_order'] ?? 0),
        ]);
        $this->flash('success', 'Skill bijgewerkt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function deleteSkill(?int $id): void {
        if ($id) $this->skills->deleteSkill($id);
        $this->flash('success', 'Skill verwijderd.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function createEducation(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/edu-create', compact('flash'), 'Opleiding toevoegen');
    }

    private function storeEducation(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=dev-life&action=edu-create'); return; }
        if (empty(trim($post['title_nl'] ?? ''))) { $this->flash('error', 'Titel (NL) is verplicht.'); header('Location: ?page=admin&section=dev-life&action=edu-create'); exit; }
        $this->skills->createEducation($post);
        $this->flash('success', 'Opleiding aangemaakt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function editEducation(?int $id): void {
        $item = $id ? $this->skills->getEducationByIdForAdmin($id) : null;
        if (!$item) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/edu-edit', compact('item', 'flash'), 'Opleiding bewerken');
    }

    private function updateEducation(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=dev-life&action=edu-edit&id={$id}"); return; }
        $this->skills->updateEducation($id, $post);
        $this->flash('success', 'Opleiding bijgewerkt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function deleteEducation(?int $id): void {
        if ($id) $this->skills->deleteEducation($id);
        $this->flash('success', 'Opleiding verwijderd.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function createGoal(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/goal-create', compact('flash'), 'Leerdoel toevoegen');
    }

    private function storeGoal(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=dev-life&action=goal-create'); return; }
        if (empty(trim($post['title_nl'] ?? ''))) { $this->flash('error', 'Titel (NL) is verplicht.'); header('Location: ?page=admin&section=dev-life&action=goal-create'); exit; }
        $this->skills->createGoal($post);
        $this->flash('success', 'Leerdoel aangemaakt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function editGoal(?int $id): void {
        $goal = $id ? $this->skills->getGoalByIdForAdmin($id) : null;
        if (!$goal) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('dev-life/goal-edit', compact('goal', 'flash'), 'Leerdoel bewerken');
    }

    private function updateGoal(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=dev-life&action=goal-edit&id={$id}"); return; }
        $this->skills->updateGoal($id, $post);
        $this->flash('success', 'Leerdoel bijgewerkt.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    private function deleteGoal(?int $id): void {
        if ($id) $this->skills->deleteGoal($id);
        $this->flash('success', 'Leerdoel verwijderd.');
        header('Location: ?page=admin&section=dev-life'); exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TAGS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeTags(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'create':
                $isPost ? $this->storeTag($_POST) : $this->showTagCreate();
                break;
            case 'edit':
                $isPost ? $this->updateTag($id, $_POST) : $this->showTagEdit($id);
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) $this->deleteTag($id);
                else { header('Location: ?page=admin&section=tags'); exit; }
                break;
            default:
                $this->listTags();
        }
    }

    private function listTags(): void {
        $tags  = $this->tags->getAll();
        $flash = $this->popFlash();
        $this->renderAdmin('tags/index', compact('tags', 'flash'), 'Tags');
    }

    private function showTagCreate(): void {
        $flash = $this->popFlash();
        $this->renderAdmin('tags/create', compact('flash'), 'Tag toevoegen');
    }

    private function storeTag(array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail('?page=admin&section=tags&action=create'); return; }
        $name = trim($post['name'] ?? '');
        $slug = trim($post['slug'] ?? '') ?: TagModel::slugify($name);
        $errors = [];
        if (!$name) $errors[] = 'Naam is verplicht.';
        if ($this->tags->nameExists($name)) $errors[] = 'Naam al in gebruik.';
        if ($this->tags->slugExists($slug)) $errors[] = 'Slug al in gebruik.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header('Location: ?page=admin&section=tags&action=create'); exit; }
        $id = $this->tags->create($name, $slug);
        ActivityLogModel::log('created', "Created tag: {$name}", 'tags', $id);
        $this->flash('success', "Tag '{$name}' aangemaakt.");
        header('Location: ?page=admin&section=tags'); exit;
    }

    private function showTagEdit(?int $id): void {
        $tag = $id ? $this->tags->getById($id) : null;
        if (!$tag) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('tags/edit', compact('tag', 'flash'), 'Tag bewerken');
    }

    private function updateTag(?int $id, array $post): void {
        if (!Auth::verifyCsrf($post['_csrf'] ?? '')) { $this->csrfFail("?page=admin&section=tags&action=edit&id={$id}"); return; }
        $tag  = $id ? $this->tags->getById($id) : null;
        if (!$tag) { $this->notFound(); return; }
        $name = trim($post['name'] ?? '');
        $slug = trim($post['slug'] ?? '') ?: TagModel::slugify($name);
        $errors = [];
        if (!$name) $errors[] = 'Naam is verplicht.';
        if ($this->tags->nameExists($name, $id)) $errors[] = 'Naam al in gebruik.';
        if ($this->tags->slugExists($slug, $id)) $errors[] = 'Slug al in gebruik.';
        if ($errors) { $this->flash('error', implode(' ', $errors)); header("Location: ?page=admin&section=tags&action=edit&id={$id}"); exit; }
        $this->tags->update($id, $name, $slug);
        ActivityLogModel::log('updated', "Updated tag: {$name}", 'tags', $id);
        $this->flash('success', 'Tag bijgewerkt.');
        header('Location: ?page=admin&section=tags'); exit;
    }

    private function deleteTag(?int $id): void {
        if ($id) {
            $tag = $this->tags->getById($id);
            $this->tags->delete($id);
            ActivityLogModel::log('deleted', "Deleted tag: " . ($tag['name'] ?? $id), 'tags', $id);
        }
        $this->flash('success', 'Tag verwijderd.');
        header('Location: ?page=admin&section=tags'); exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // NEWS COMMENTS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeComments(string $action, ?int $id, bool $isPost): void {
        switch ($action) {
            case 'approve':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) $this->approveComment($id);
                else { header('Location: ?page=admin&section=comments'); exit; }
                break;
            case 'delete':
                if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) $this->deleteComment($id);
                else { header('Location: ?page=admin&section=comments'); exit; }
                break;
            default:
                $this->listComments();
        }
    }

    private function listComments(): void {
        $comments = $this->comments->getAllForAdmin();
        $flash    = $this->popFlash();
        $this->renderAdmin('comments/index', compact('comments', 'flash'), 'Reacties');
    }

    private function approveComment(?int $id): void {
        if ($id) {
            $c = $this->comments->getById($id);
            $this->comments->approve($id);
            ActivityLogModel::log('approved', "Approved comment by " . ($c['username'] ?? '?'), 'news_comments', $id);
        }
        $this->flash('success', 'Reactie goedgekeurd.');
        header('Location: ?page=admin&section=comments'); exit;
    }

    private function deleteComment(?int $id): void {
        if ($id) {
            $c = $this->comments->getById($id);
            $this->comments->delete($id);
            ActivityLogModel::log('deleted', "Deleted comment by " . ($c['username'] ?? '?'), 'news_comments', $id);
        }
        $this->flash('success', 'Reactie verwijderd.');
        header('Location: ?page=admin&section=comments'); exit;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ACTIVITY LOGS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeActivityLogs(string $action, ?int $id, bool $isPost): void {
        if ($action === 'delete' && $isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            if ($id) $this->activityLog->delete($id);
            $this->flash('success', 'Log verwijderd.');
            header('Location: ?page=admin&section=activity-logs'); exit;
        }
        if ($action === 'clear' && $isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $days    = max(1, (int)($_POST['older_than'] ?? 30));
            $deleted = $this->activityLog->clearOlderThan($days);
            ActivityLogModel::log('deleted', "Cleared {$deleted} activity logs older than {$days} days");
            $this->flash('success', "{$deleted} logs ouder dan {$days} dagen verwijderd.");
            header('Location: ?page=admin&section=activity-logs'); exit;
        }
        $filters = [
            'action' => $_GET['action_filter'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];
        $perPage = 25;
        $page    = max(1, (int)($_GET['p'] ?? 1));
        $offset  = ($page - 1) * $perPage;
        $logs    = $this->activityLog->getAll($perPage, $offset, $filters);
        $total   = $this->activityLog->countAll($filters);
        $actions = $this->activityLog->getDistinctActions();
        $flash   = $this->popFlash();
        $this->renderAdmin('activity-logs/index', compact('logs', 'total', 'page', 'perPage', 'filters', 'actions', 'flash'), 'Activity Log');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // SITE SETTINGS
    // ═══════════════════════════════════════════════════════════════════════

    private function routeSettings(string $action, bool $isPost): void {
        if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $allSettings = $this->settings->getAll();
            $this->settings->updateAll($_POST, $allSettings);
            ActivityLogModel::log('updated', "Updated site settings");
            $this->flash('success', 'Instellingen opgeslagen.');
            header('Location: ?page=admin&section=settings'); exit;
        }
        $settings = $this->settings->getAllGrouped();
        $flash    = $this->popFlash();
        $this->renderAdmin('settings/index', compact('settings', 'flash'), 'Site Instellingen');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PROFILE (own account)
    // ═══════════════════════════════════════════════════════════════════════

    private function routeProfile(string $action, bool $isPost): void {
        $authUser = Auth::user();
        if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $post = $_POST;

            // Change password
            if (!empty($post['current_password'])) {
                $db   = Database::getConnection();
                $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                $stmt->execute([':id' => $authUser['id']]);
                $hash = $stmt->fetchColumn();
                if (!password_verify($post['current_password'], $hash)) {
                    $this->flash('error', 'Huidig wachtwoord klopt niet.');
                    header('Location: ?page=admin&section=profile'); exit;
                }
                if (strlen($post['new_password'] ?? '') < 8) {
                    $this->flash('error', 'Nieuw wachtwoord minimaal 8 tekens.');
                    header('Location: ?page=admin&section=profile'); exit;
                }
                if ($post['new_password'] !== $post['confirm_password']) {
                    $this->flash('error', 'Wachtwoorden komen niet overeen.');
                    header('Location: ?page=admin&section=profile'); exit;
                }
                $this->users->updatePassword($authUser['id'], $post['new_password']);
                ActivityLogModel::log('updated', "Changed own password");
                $this->flash('success', 'Wachtwoord gewijzigd.');
                header('Location: ?page=admin&section=profile'); exit;
            }

            // Profile photo
            $photoPath = null;
            if (!empty($_FILES['profile_photo']['name'])) {
                $photoPath = $this->handleImageUpload($_FILES['profile_photo'], 'avatars');
            }

            $this->users->updateProfile($authUser['id'], [
                'about'              => trim($post['about'] ?? ''),
                'birthday'           => $post['birthday'] ?? '',
                'public_profile'     => isset($post['public_profile']) ? 1 : 0,
                'preferred_language' => $post['preferred_language'] ?? 'nl',
            ]);
            if ($photoPath) {
                $this->users->updateProfilePhoto($authUser['id'], $photoPath);
            }

            // Keep auth session in sync after profile changes (language/avatar/etc.).
            $freshUser = $this->users->getById((int) $authUser['id']);
            if ($freshUser) {
                $_SESSION['auth_user'] = [
                    'id'                 => $freshUser['id'],
                    'username'           => $freshUser['username'],
                    'email'              => $freshUser['email'],
                    'role'               => $freshUser['role'],
                    'profile_photo_path' => $freshUser['profile_photo_path'] ?? null,
                    'preferred_language' => $freshUser['preferred_language'] ?? 'nl',
                ];
            }

            ActivityLogModel::log('updated', "Updated own profile");
            $this->flash('success', 'Profiel opgeslagen.');
            header('Location: ?page=admin&section=profile'); exit;
        }

        $user  = $this->users->getById($authUser['id']);
        $flash = $this->popFlash();
        $this->renderAdmin('profile/edit', compact('user', 'flash'), 'Mijn profiel');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function renderAdmin(string $view, array $data = [], string $title = 'Admin'): void {
        $data['pageTitle'] = $title;
        $data['authUser']  = Auth::user();
        try {
            $data['unreadMessages']   = $this->contact->countUnread();
            $data['pendingComments']  = $this->comments->countPending();
        } catch (\Throwable $e) {
            $data['unreadMessages']  = 0;
            $data['pendingComments'] = 0;
        }
        extract($data);

        ob_start();
        $viewFile = __DIR__ . "/../Views/admin/{$view}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "<p>View not found: {$view}</p>";
        }
        $content = ob_get_clean();
        include __DIR__ . '/../Views/admin/layout.php';
    }

    private function handleMultiImageUpload(array $filesInput, string $subfolder): array {
        $paths = [];
        // $_FILES['gallery_images'] comes as parallel arrays; normalize to list of single-file arrays
        $count = count($filesInput['name'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            if (($filesInput['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            $single = [
                'name'     => $filesInput['name'][$i],
                'type'     => $filesInput['type'][$i],
                'tmp_name' => $filesInput['tmp_name'][$i],
                'error'    => $filesInput['error'][$i],
                'size'     => $filesInput['size'][$i],
            ];
            $path = $this->handleImageUpload($single, $subfolder);
            if ($path !== null) {
                $paths[] = $path;
            }
        }
        return $paths;
    }

    private function handleImageUpload(?array $file, string $subfolder): ?string {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $this->detectImageMimeType($file['tmp_name'] ?? '');
        if ($mimeType === null || !in_array($mimeType, $allowed, true)) {
            return null;
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dir      = __DIR__ . "/../../public/images/uploads/{$subfolder}/";

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $dest = $dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return "public/images/uploads/{$subfolder}/{$filename}";
        }
        return null;
    }

    private function detectImageMimeType(string $filePath): ?string {
        if ($filePath === '' || !is_file($filePath)) {
            return null;
        }

        if (!function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
    }

    private function parseTech(string $raw): array {
        return array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $raw))));
    }

    private function parseFeatures(string $raw): array {
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }

    private function syncProjectRoadmapIfPossible(array $project): ?string {
        $repoUrl = trim((string) ($project['repo_url'] ?? ''));
        if ($repoUrl === '') {
            return null;
        }

        try {
            $result = $this->projectRoadmapService->syncProjectRoadmap($project, Auth::user());
            if (($result['ok'] ?? false) === true) {
                return 'Roadmap-sync OK (' . (int) ($result['itemCount'] ?? 0) . ' TODOs).';
            }
            return 'Roadmap-sync mislukt: ' . (string) ($result['error'] ?? 'onbekende fout') . '.';
        } catch (\Throwable $e) {
            return 'Roadmap-sync exception: ' . $e->getMessage() . '.';
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // WIP PAGES (owner only)
    // ═══════════════════════════════════════════════════════════════════════

    private function routeWip(bool $isPost): void {
        $configFile = __DIR__ . '/../../app/Config/wip_pages.json';
        $knownPages = [
            'home'       => 'Home / About',
            'dev-life'   => 'Dev Life',
            'games'      => 'Games',
            'projects'   => 'Projects',
            'news'       => 'News',
            'faq'        => 'FAQ',
            'readmesync' => 'ReadmeSync',
            'contact'    => 'Contact',
        ];

        if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $selected = array_keys(array_filter($_POST['wip'] ?? [], fn($v) => $v === '1'));
            $selected = array_values(array_intersect($selected, array_keys($knownPages)));
            file_put_contents($configFile, json_encode($selected, JSON_PRETTY_PRINT));
            ActivityLogModel::log('updated', 'Updated WIP pages: ' . (implode(', ', $selected) ?: 'none'));
            $this->flash('success', 'WIP-pagina\'s opgeslagen.');
            header('Location: ?page=admin&section=wip'); exit;
        }

        $current = [];
        if (file_exists($configFile)) {
            $current = json_decode(file_get_contents($configFile), true) ?? [];
        }

        $flash = $this->popFlash();
        $this->renderAdmin('wip/index', compact('knownPages', 'current', 'flash'), 'WIP Pagina\'s');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ROADMAP (owner only)
    // ═══════════════════════════════════════════════════════════════════════

    private function routeRoadmap(bool $isPost): void {
        $config = $this->loadRoadmapConfig();

        if ($isPost && Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
            $action = $_POST['roadmap_action'] ?? 'save';

            if ($action === 'sync_markdown') {
                $markdownSource = trim((string) ($_POST['markdown_source'] ?? ''));
                $todosOnly = isset($_POST['todos_only']) && $_POST['todos_only'] === '1';
                $mergeMode = isset($_POST['merge_mode']) && $_POST['merge_mode'] === '1';

                if ($markdownSource === '') {
                    $this->flash('error', 'Vul eerst roadmap markdown in.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $items = $this->parseChecklistItems($markdownSource, $todosOnly);
                if (empty($items)) {
                    $this->flash('error', 'Geen roadmap-, checklist- of TODO-items gevonden in de ingevoerde markdown.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                if ($mergeMode) {
                    $items = $this->mergeRoadmapItems((array) ($config['items'] ?? []), $items);
                }

                $config['items'] = $items;
                $config['source'] = 'manual-markdown';
                $config['lastSyncAt'] = date('c');
                $config['markdownSource'] = $markdownSource;
                $this->saveRoadmapConfig($config);

                ActivityLogModel::log('updated', 'Synced roadmap from admin markdown (' . count($items) . ' items, merge=' . ($mergeMode ? 'yes' : 'no') . ')');
                $this->flash('success', 'Roadmap bijgewerkt vanuit de markdown op deze pagina.');
                header('Location: ?page=admin&section=roadmap'); exit;
            }

            if ($action === 'sync') {
                $repoUrl = trim($_POST['repo_url'] ?? '');
                $todosOnly = isset($_POST['todos_only']) && $_POST['todos_only'] === '1';
                $mergeMode = isset($_POST['merge_mode']) && $_POST['merge_mode'] === '1';

                // TODO(roadmap): [P3] add optional "target section" input so parsing can focus on a single README block (e.g. Roadmap/TODO).

                $normalizedRepoUrl = $this->normalizeGitHubRepoUrl($repoUrl);
                if ($normalizedRepoUrl === null) {
                    $this->flash('error', 'Geef een geldige GitHub repo URL op.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $apiError = null;
                $readmeSyncData = $this->fetchReadmeSyncData($normalizedRepoUrl, $apiError);
                if ($readmeSyncData === null) {
                    $this->flash('error', $apiError ?: 'ReadmeSync synchronisatie mislukt.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                if (!$this->apiResponseHasTodosField($readmeSyncData)) {
                    $responseKeys = implode(', ', array_keys($readmeSyncData));
                    $this->flash('error', 'ReadmeSync API is verouderd of fout gedeployed: verwacht todos-veld, ontvangen keys: ' . ($responseKeys !== '' ? $responseKeys : '(geen)') . '.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $items = $this->extractRoadmapItemsFromApiTodos($readmeSyncData, $todosOnly);

                if (empty($items)) {
                    $this->flash('warning', 'Geen echte TODO-items gevonden in de repository code.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $existingItems = $this->cleanupRoadmapItems((array) ($config['items'] ?? []), true, false);

                if ($mergeMode) {
                    $items = $this->mergeRoadmapItems($existingItems, $items);
                }

                $config['items'] = $items;
                $config['repoUrl'] = $normalizedRepoUrl;
                $config['source'] = 'readmesync-todos';
                $config['lastSyncAt'] = date('c');
                $config['markdownSource'] = trim((string) ($readmeSyncData['content'] ?? ''));
                $this->saveRoadmapConfig($config);

                ActivityLogModel::log('updated', 'Synced roadmap from ReadmeSync API TODOs (' . count($items) . ' items, merge=' . ($mergeMode ? 'yes' : 'no') . ')');
                $this->flash('success', $mergeMode
                    ? 'Roadmap gemerged en gesynchroniseerd vanuit API TODO-items.'
                    : 'Roadmap gesynchroniseerd vanuit API TODO-items.');
                header('Location: ?page=admin&section=roadmap'); exit;
            }

            if ($action === 'cleanup') {
                $before = count((array) ($config['items'] ?? []));
                $config['items'] = $this->cleanupRoadmapItems((array) ($config['items'] ?? []), true, true);
                $after = count((array) ($config['items'] ?? []));
                $config['source'] = 'manual';
                $this->saveRoadmapConfig($config);
                ActivityLogModel::log('updated', 'Roadmap cleanup uitgevoerd (' . $before . ' -> ' . $after . ' items)');
                $this->flash('success', 'Roadmap opgeschoond: ' . max(0, $before - $after) . ' items verwijderd (test/afgerond).');
                header('Location: ?page=admin&section=roadmap'); exit;
            }

            if ($action === 'reset') {
                $config = [
                    'source' => 'manual',
                    'repoUrl' => '',
                    'lastSyncAt' => null,
                    'markdownSource' => '',
                    'items' => $this->getDefaultRoadmapItems(),
                ];
                $this->saveRoadmapConfig($config);
                ActivityLogModel::log('updated', 'Reset roadmap to defaults');
                $this->flash('success', 'Roadmap teruggezet naar standaarditems.');
                header('Location: ?page=admin&section=roadmap'); exit;
            }

            // save
            $doneIds = array_flip((array) ($_POST['done'] ?? []));
            $updatedItems = [];
            foreach ($config['items'] as $item) {
                $id = (string) ($item['id'] ?? '');
                $item['status'] = isset($doneIds[$id]) ? 'done' : 'todo';
                $updatedItems[] = $item;
            }
            $config['items'] = $updatedItems;
            $config['source'] = 'manual';
            $this->saveRoadmapConfig($config);

            ActivityLogModel::log('updated', 'Updated roadmap statuses manually');
            $this->flash('success', 'Roadmap status opgeslagen.');
            header('Location: ?page=admin&section=roadmap'); exit;
        }

        $flash = $this->popFlash();
        $this->renderAdmin('roadmap/index', compact('config', 'flash'), 'Roadmap Beheer');
    }

    private function routeTelemetry(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
                $this->flash('error', 'Ongeldige CSRF token. Probeer opnieuw.');
                header('Location: ?page=admin&section=telemetry');
                exit;
            }

            $postAction = trim((string) ($_POST['telemetry_action'] ?? ''));
            if ($postAction === 'delete_filtered') {
                $deleteFilters = [
                    'eventType' => trim((string) ($_POST['eventType'] ?? '')),
                    'repo'      => trim((string) ($_POST['repo'] ?? '')),
                    'actor'     => trim((string) ($_POST['actor'] ?? '')),
                    'language'  => trim((string) ($_POST['language'] ?? '')),
                    'os'        => trim((string) ($_POST['os'] ?? '')),
                    'fromUtc'   => trim((string) ($_POST['fromUtc'] ?? '')),
                    'toUtc'     => trim((string) ($_POST['toUtc'] ?? '')),
                    'onlyFailures' => isset($_POST['onlyFailures']) ? 'true' : '',
                    'take' => isset($_POST['take']) && is_numeric($_POST['take']) ? (string) (int) $_POST['take'] : '',
                ];

                if (empty(array_filter($deleteFilters, static fn($value) => $value !== ''))) {
                    $this->flash('error', 'Verwijderen zonder filter is geblokkeerd. Zet minimaal 1 filter.');
                    header('Location: ?page=admin&section=telemetry');
                    exit;
                }

                $deleteError = null;
                $result = $this->deleteReadmeSyncTelemetry($deleteFilters, $deleteError);
                if ($result === null) {
                    $this->flash('error', $deleteError ?: 'Verwijderen van telemetry mislukt.');
                } else {
                    $deleted = (int) ($result['deleted'] ?? 0);
                    $this->flash('success', 'Telemetry opgeschoond. Verwijderde logs: ' . $deleted . '.');
                }

                $redirectFilters = array_filter([
                    'eventType' => $deleteFilters['eventType'],
                    'repo' => $deleteFilters['repo'],
                    'actor' => $deleteFilters['actor'],
                    'language' => $deleteFilters['language'],
                    'os' => $deleteFilters['os'],
                    'fromUtc' => $deleteFilters['fromUtc'],
                    'toUtc' => $deleteFilters['toUtc'],
                    'groupBy' => trim((string) ($_POST['groupBy'] ?? '')),
                ], static fn($value) => $value !== '');

                $query = http_build_query(array_merge(['page' => 'admin', 'section' => 'telemetry'], $redirectFilters));
                header('Location: ?' . $query);
                exit;
            }
        }

        $filters = [
            'telemetry_page' => max(1, (int) ($_GET['telemetry_page'] ?? 1)),
            'eventType'      => trim($_GET['eventType'] ?? ''),
            'repo'           => trim($_GET['repo'] ?? ''),
            'actor'          => trim($_GET['actor'] ?? ''),
            'language'       => trim($_GET['language'] ?? ''),
            'os'             => trim($_GET['os'] ?? ''),
            'fromUtc'        => trim($_GET['fromUtc'] ?? ''),
            'toUtc'          => trim($_GET['toUtc'] ?? ''),
        ];
        $groupBy = trim((string) ($_GET['groupBy'] ?? 'none'));
        if (!in_array($groupBy, ['none', 'repo', 'actor'], true)) {
            $groupBy = 'none';
        }

        $apiError = null;
        $telemetry = $this->fetchReadmeSyncTelemetry($filters, $apiError) ?? [];
        $summary = is_array($telemetry['summary'] ?? null) ? $telemetry['summary'] : [];
        $items = is_array($telemetry['items'] ?? null) ? $telemetry['items'] : [];
        $groupedItems = $groupBy === 'none' ? [] : $this->buildTelemetryGroups($items, $groupBy);

        $viewData = [
            'telemetry' => $telemetry,
            'summary' => $summary,
            'items' => $items,
            'groupedItems' => $groupedItems,
            'filters' => $filters,
            'groupBy' => $groupBy,
            'apiError' => $apiError,
            'exportUrl' => $this->buildReadmeSyncTelemetryUrl(true, $filters),
            'listUrl' => $this->buildReadmeSyncTelemetryUrl(false, $filters),
        ];

        $flash = $this->popFlash();
        $this->renderAdmin('telemetry/index', array_merge($viewData, compact('flash')), 'Telemetry');
    }

    private function roadmapConfigPath(): string {
        return __DIR__ . '/../../app/Config/roadmap_items.json';
    }

    private function fetchReadmeSyncTelemetry(array $filters, ?string &$error): ?array {
        $error = null;

        if (!function_exists('curl_init')) {
            $error = 'cURL niet beschikbaar op server.';
            return null;
        }

        if ($this->readmeSyncAdminApiKey === '') {
            $error = 'ReadmeSync admin API-key ontbreekt op de server. Zet READMESYNC_ADMIN_API_KEY in .env (project root of server) of als server environment variable.';
            return null;
        }

        $url = $this->buildReadmeSyncTelemetryUrl(false, $filters);
        $headers = [
            'Accept: application/json',
            'X-API-Key: ' . $this->readmeSyncAdminApiKey,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $curlErrLower = strtolower($curlErr);
            if (strpos($curlErrLower, 'ssl') !== false || strpos($curlErrLower, 'certificate') !== false) {
                $ch2 = curl_init($url);
                curl_setopt_array($ch2, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 25,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]);
                $raw2 = curl_exec($ch2);
                $httpCode2 = (int) curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                $curlErr2 = curl_error($ch2);
                curl_close($ch2);

                if (!$curlErr2) {
                    $raw = $raw2;
                    $httpCode = $httpCode2;
                    $curlErr = '';
                }
            }
        }

        if ($curlErr) {
            $error = 'ReadmeSync telemetry niet bereikbaar: ' . $curlErr;
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        if ($httpCode !== 200) {
            $detail = is_array($decoded) ? (string) ($decoded['detail'] ?? $decoded['title'] ?? '') : '';
            $error = $detail !== '' ? $detail : 'ReadmeSync telemetry gaf HTTP ' . $httpCode . '.';
            return null;
        }

        if (!is_array($decoded)) {
            $error = 'ReadmeSync telemetry response is ongeldig.';
            return null;
        }

        return $decoded;
    }

    private function buildReadmeSyncTelemetryUrl(bool $export, array $filters): string {
        $baseUrl = $export ? $this->readmeSyncTelemetryExportUrl : $this->readmeSyncTelemetryApiUrl;
        $query = array_filter($filters, static fn($value) => $value !== null && $value !== '');

        if ($export) {
            unset($query['telemetry_page']);
        } else {
            $query['page'] = $query['telemetry_page'] ?? 1;
            $query['pageSize'] = 50;
            unset($query['telemetry_page']);
        }

        if (empty($query)) {
            return $baseUrl;
        }

        return $baseUrl . '?' . http_build_query($query);
    }

    private function deleteReadmeSyncTelemetry(array $filters, ?string &$error): ?array {
        $error = null;

        if (!function_exists('curl_init')) {
            $error = 'cURL niet beschikbaar op server.';
            return null;
        }

        if ($this->readmeSyncAdminApiKey === '') {
            $error = 'ReadmeSync admin API-key ontbreekt op de server.';
            return null;
        }

        $query = array_filter($filters, static fn($value) => $value !== null && $value !== '');
        $url = $this->readmeSyncTelemetryApiUrl . (empty($query) ? '' : ('?' . http_build_query($query)));

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-API-Key: ' . $this->readmeSyncAdminApiKey,
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $error = 'ReadmeSync telemetry delete niet bereikbaar: ' . $curlErr;
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        if ($httpCode !== 200) {
            $detail = is_array($decoded) ? (string) ($decoded['detail'] ?? $decoded['title'] ?? '') : '';
            $error = $detail !== '' ? $detail : 'ReadmeSync telemetry delete gaf HTTP ' . $httpCode . '.';
            return null;
        }

        if (!is_array($decoded)) {
            $error = 'ReadmeSync telemetry delete response is ongeldig.';
            return null;
        }

        return $decoded;
    }

    private function buildTelemetryGroups(array $items, string $groupBy): array {
        $groups = [];

        foreach ($items as $item) {
            $repoOwner = trim((string) ($item['repoOwner'] ?? ''));
            $repoName = trim((string) ($item['repoName'] ?? ''));
            $repoLabel = ($repoOwner !== '' && $repoName !== '') ? ($repoOwner . '/' . $repoName) : trim((string) ($item['repoUrl'] ?? ''));
            if ($repoLabel === '') {
                $repoLabel = 'onbekend';
            }

            $sourceClient = trim((string) ($item['sourceClient'] ?? 'portfolio'));
            $sourceUserId = trim((string) ($item['sourceUserId'] ?? ''));
            $sourceUserName = trim((string) ($item['sourceUserName'] ?? ''));
            $actorLabel = $sourceUserName !== ''
                ? $sourceUserName . ($sourceUserId !== '' ? (' (#' . $sourceUserId . ')') : '')
                : ($sourceUserId !== '' ? ('user #' . $sourceUserId) : $sourceClient);

            $eventType = trim((string) ($item['eventType'] ?? 'unknown'));
            $groupLabel = $groupBy === 'actor' ? $actorLabel : $repoLabel;
            $key = strtolower($groupLabel . '|' . $eventType);

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'groupLabel' => $groupLabel,
                    'repoLabel' => $repoLabel,
                    'actorLabel' => $actorLabel,
                    'sourceClient' => $sourceClient,
                    'sourceUserId' => $sourceUserId,
                    'sourceUserName' => $sourceUserName,
                    'eventType' => $eventType,
                    'count' => 0,
                    'successCount' => 0,
                    'failureCount' => 0,
                    'lastCreatedAt' => null,
                ];
            }

            $groups[$key]['count']++;
            $isSuccess = (bool) ($item['success'] ?? false);
            if ($isSuccess) {
                $groups[$key]['successCount']++;
            } else {
                $groups[$key]['failureCount']++;
            }

            $createdAt = (string) ($item['createdAt'] ?? '');
            if ($createdAt !== '') {
                $last = (string) ($groups[$key]['lastCreatedAt'] ?? '');
                if ($last === '' || strtotime($createdAt) > strtotime($last)) {
                    $groups[$key]['lastCreatedAt'] = $createdAt;
                }
            }
        }

        $result = array_values($groups);
        usort($result, static function (array $a, array $b): int {
            return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
        });

        return $result;
    }

    private function getDefaultRoadmapItems(): array {
        return [
            ['id' => 'tags', 'title' => 'Tags', 'description' => 'Many-to-many tags op nieuwsberichten, tag-filter op news-pagina', 'status' => 'done'],
            ['id' => 'news-comments', 'title' => 'News comments', 'description' => 'Reacties op nieuwsberichten met moderatie en goedkeuringsflow', 'status' => 'done'],
            ['id' => 'activity-logs', 'title' => 'Activity logs', 'description' => 'Alle admin-acties worden bijgehouden met filter en paginering', 'status' => 'done'],
            ['id' => 'site-settings', 'title' => 'Site settings', 'description' => 'Dynamische configuratie per groep instelbaar via admin', 'status' => 'done'],
            ['id' => 'profiles-auth', 'title' => 'User profiles + auth', 'description' => 'Publiek registreren, inloggen, profielpagina en comment-auth', 'status' => 'done'],
        ];
    }

    private function loadRoadmapConfig(): array {
        $path = $this->roadmapConfigPath();
        $defaults = [
            'source' => 'manual',
            'repoUrl' => '',
            'lastSyncAt' => null,
            'markdownSource' => '',
            'items' => $this->getDefaultRoadmapItems(),
        ];

        if (!file_exists($path)) {
            $this->saveRoadmapConfig($defaults);
            return $defaults;
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $decoded['items'] = is_array($decoded['items'] ?? null) ? $decoded['items'] : $defaults['items'];
        $decoded['source'] = $decoded['source'] ?? 'manual';
        $decoded['repoUrl'] = $decoded['repoUrl'] ?? '';
        $decoded['lastSyncAt'] = $decoded['lastSyncAt'] ?? null;
        $decoded['markdownSource'] = is_string($decoded['markdownSource'] ?? null) ? $decoded['markdownSource'] : '';

        // Migrate previously seeded wrong owner for this repository URL.
        if (is_string($decoded['repoUrl']) && preg_match('#^https?://github\.com/tombomeke-ehb/Portfolio(?:$|[/?#])#i', $decoded['repoUrl'])) {
            $decoded['repoUrl'] = 'https://github.com/tombomeke/Portfolio';
        }

        return $decoded;
    }

    private function saveRoadmapConfig(array $config): void {
        $path = $this->roadmapConfigPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function fetchReadmeSyncData(string $repoUrl, ?string &$error): ?array {
        $error = null;
        if (!function_exists('curl_init')) {
            $error = 'cURL niet beschikbaar op server.';
            return null;
        }

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

        $payload = json_encode([
            'githubRepoUrl' => $repoUrl,
            'clientApp' => 'portfolio',
            'userId' => $resolvedUserId,
            'userName' => $resolvedUserName,
        ]);
        // Uses the real ReadmeSync API that powers the public ReadmeSync page.
        $ch = curl_init($this->readmeSyncApiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        // SSL fallback: shared hosting can have outdated CA bundles.
        if ($curlErr) {
            $curlErrLower = strtolower($curlErr);
            if (strpos($curlErrLower, 'ssl') !== false || strpos($curlErrLower, 'certificate') !== false) {
                $ch2 = curl_init($this->readmeSyncApiUrl);
                $fallback = [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT => 35,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ];
                curl_setopt_array($ch2, $fallback);
                $raw2 = curl_exec($ch2);
                $httpCode2 = (int) curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                $curlErr2 = curl_error($ch2);
                curl_close($ch2);

                if (!$curlErr2) {
                    $raw = $raw2;
                    $httpCode = $httpCode2;
                    $curlErr = '';
                }
            }
        }

        if ($curlErr) {
            $error = 'ReadmeSync niet bereikbaar: ' . $curlErr;
            return null;
        }
        if ($httpCode !== 200) {
            $decoded = json_decode((string) $raw, true);
            $detail = strtolower((string) ($decoded['detail'] ?? $decoded['error'] ?? $raw ?? ''));
            if (strpos($detail, 'ssl') !== false || strpos($detail, 'certificate') !== false) {
                $error = 'ReadmeSync backend SSL-probleem: certificaat verlopen of ongeldig.';
            } elseif ($httpCode === 404) {
                $repoPublic = $this->isGitHubRepoPublic($repoUrl);
                if ($repoPublic === true) {
                    $error = 'Repository is publiek bereikbaar, maar ReadmeSync API kan GitHub niet ophalen (controleer API GitHub token/certificaat).';
                } elseif ($repoPublic === false) {
                    $error = 'Repository niet gevonden of privé. Controleer owner/repo URL en GitHub-toegang van de API.';
                } else {
                    $error = 'Repository niet gevonden of API kan GitHub niet bereiken. Controleer URL, API GitHub token en serverconnectiviteit.';
                }
            } else {
                $error = 'ReadmeSync gaf HTTP ' . $httpCode . '.';
            }
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            $error = 'ReadmeSync response is ongeldig JSON.';
            return null;
        }

        return $decoded;
    }

    private function extractRoadmapItemsFromApiTodos(array $readmeSyncData, bool $todosOnly): array {
        $todoEntries = $this->findApiTodoEntries($readmeSyncData);
        if (empty($todoEntries)) {
            return [];
        }

        $items = [];
        $seenKeys = [];

        foreach ($todoEntries as $index => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $rawTitle = trim((string) (
                $entry['text']
                ?? $entry['title']
                ?? $entry['todo']
                ?? $entry['task']
                ?? $entry['message']
                ?? ''
            ));

            if ($rawTitle === '') {
                continue;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($rawTitle);
            if ($title === '') {
                continue;
            }

            $statusValue = strtolower((string) ($entry['status'] ?? 'todo'));
            $isDone = $statusValue === 'done' || $statusValue === 'completed' || !empty($entry['done']);
            $status = $isDone ? 'done' : 'todo';

            if ($todosOnly && $status === 'done') {
                continue;
            }

            $sourceFile = trim((string) (
                $entry['file']
                ?? $entry['path']
                ?? $entry['filePath']
                ?? ($entry['location']['file'] ?? '')
            ));

            $sourceLine = (int) (
                $entry['line']
                ?? $entry['lineNumber']
                ?? ($entry['location']['line'] ?? 0)
            );

            $normalizedTitle = $this->normalizeRoadmapTitle($title);
            $dedupeKey = $normalizedTitle . '|' . strtolower($sourceFile !== '' ? $sourceFile : 'todo');
            if ($normalizedTitle === '' || isset($seenKeys[$dedupeKey])) {
                continue;
            }
            $seenKeys[$dedupeKey] = true;

            $priorityValue = strtolower((string) ($entry['priority'] ?? ''));
            if (in_array($priorityValue, ['high', 'medium', 'low', 'normal'], true)) {
                $priority = $priorityValue;
            }

            $description = trim((string) ($entry['description'] ?? ''));

            $items[] = [
                'id' => 'todo-' . ($index + 1) . '-' . substr(md5($title . '|' . $sourceFile . '|' . $sourceLine), 0, 8),
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'sourceLine' => $sourceLine > 0 ? $sourceLine : null,
                'sourceSection' => $sourceFile !== '' ? $sourceFile : 'todo',
                'syncSource' => 'readmesync-todos',
            ];
        }

        return $items;
    }

    private function findApiTodoEntries(array $payload): array {
        $candidatePaths = [
            ['todos'],
            ['todoItems'],
            ['result', 'todos'],
            ['results', 'todos'],
            ['data', 'todos'],
            ['analysis', 'todos'],
        ];

        foreach ($candidatePaths as $path) {
            $candidate = $this->readArrayPath($payload, $path);
            if ($this->isTodoEntryList($candidate)) {
                return $candidate;
            }
        }

        return $this->findTodoEntriesRecursive($payload);
    }

    private function apiResponseHasTodosField(array $payload): bool {
        return array_key_exists('todos', $payload)
            || array_key_exists('todoItems', $payload)
            || (is_array($payload['result'] ?? null) && array_key_exists('todos', $payload['result']))
            || (is_array($payload['results'] ?? null) && array_key_exists('todos', $payload['results']))
            || (is_array($payload['data'] ?? null) && array_key_exists('todos', $payload['data']))
            || (is_array($payload['analysis'] ?? null) && array_key_exists('todos', $payload['analysis']));
    }

    private function cleanupRoadmapItems(array $items, bool $removeTestItems = true, bool $removeDoneItems = true): array {
        $cleaned = [];

        foreach ($items as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));
            $status = strtolower((string) ($item['status'] ?? 'todo'));
            $sourceSection = strtolower(trim((string) ($item['sourceSection'] ?? '')));
            $syncSource = strtolower(trim((string) ($item['syncSource'] ?? '')));

            if ($removeDoneItems && $status === 'done') {
                continue;
            }

            if ($removeTestItems) {
                $isLegacySection = $sourceSection === 'roadmap' || $sourceSection === 'todo';
                $isLegacySample = preg_match('/\b(voeg item|fallback naar todo|testregel|nog een task|extra sync test|\[todo\]|fix navbar op mobiel)\b/i', $title) === 1;
                $isOpsConnectiviteitNoise = ($sourceSection === 'ops' || $syncSource === 'manual')
                    && preg_match('/\bfix readmesync api\/github connectiviteit\b/i', $title) === 1;
                $isTestDescription = preg_match('/\b(sync test|fallback|test)\b/i', $description) === 1;

                if ($isLegacySection || $isLegacySample || $isOpsConnectiviteitNoise || $isTestDescription) {
                    continue;
                }
            }

            $cleaned[] = $item;
        }

        return array_values($cleaned);
    }

    private function readArrayPath(array $payload, array $path) {
        $current = $payload;
        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }
        return $current;
    }

    private function findTodoEntriesRecursive($value): array {
        if (!is_array($value)) {
            return [];
        }

        if ($this->isTodoEntryList($value)) {
            return $value;
        }

        foreach ($value as $nested) {
            $found = $this->findTodoEntriesRecursive($nested);
            if (!empty($found)) {
                return $found;
            }
        }

        return [];
    }

    private function isTodoEntryList($value): bool {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $first = reset($value);
        if (!is_array($first)) {
            return false;
        }

        $todoLikeKeys = ['text', 'title', 'todo', 'task', 'message'];
        foreach ($todoLikeKeys as $key) {
            if (array_key_exists($key, $first)) {
                return true;
            }
        }

        return false;
    }

    private function parseChecklistItems(string $markdown, bool $todosOnly): array {
        $cleanMarkdown = preg_replace('/```.*?```/s', '', $markdown) ?? $markdown;
        $lines = preg_split('/\R/', $cleanMarkdown) ?: [];
        $sectionLines = $this->extractRoadmapSectionLines($lines);
        $targetLines = !empty($sectionLines) ? $sectionLines : $lines;
        $hasSectionTargeting = !empty($sectionLines);

        $items = [];
        $seenNormalizedTitles = [];

        // TODO(roadmap): [P3] keep source line numbers to improve traceability in roadmap UI.

        foreach ($targetLines as $lineIndex => $lineEntry) {
            $line = is_array($lineEntry) ? (string) ($lineEntry['line'] ?? '') : (string) $lineEntry;
            $lineNumber = is_array($lineEntry) && isset($lineEntry['lineNumber'])
                ? (int) $lineEntry['lineNumber']
                : $lineIndex + 1;
            $section = is_array($lineEntry) ? (string) ($lineEntry['section'] ?? '') : '';

            $parsed = $this->parseRoadmapLine($line);
            if ($parsed === null) {
                continue;
            }

            $status = $parsed['status'];
            $title = $parsed['title'];
            $priority = (string) ($parsed['priority'] ?? 'normal');
            if ($todosOnly && $status === 'done') {
                continue;
            }

            $normalizedTitle = $this->normalizeRoadmapTitle($title);
            if ($normalizedTitle === '' || isset($seenNormalizedTitles[$normalizedTitle])) {
                continue;
            }
            $seenNormalizedTitles[$normalizedTitle] = true;

            $items[] = [
                'id' => 'synced-' . $lineNumber . '-' . substr(md5($title), 0, 8),
                'title' => $title,
                'description' => '',
                'status' => $status,
                'priority' => $priority,
                'sourceLine' => $lineNumber,
                'sourceSection' => $hasSectionTargeting ? ($section !== '' ? $section : 'roadmap') : null,
            ];
        }

        return $items;
    }

    private function extractRoadmapSectionLines(array $lines): array {
        $sectionLines = [];
        $activeSection = null;
        $foundTargetSection = false;

        foreach ($lines as $lineNumber => $line) {
            $text = trim((string) $line);
            if ($text === '') {
                if ($activeSection !== null) {
                    $sectionLines[] = [
                        'line' => $line,
                        'lineNumber' => $lineNumber + 1,
                        'section' => $activeSection,
                    ];
                }
                continue;
            }

            if (preg_match('/^#{1,6}\s*(.+?)\s*$/', $text, $headingMatch)) {
                $heading = trim((string) $headingMatch[1]);
                if (preg_match('/^(roadmap|todo)(?:\b|\s*[:\-].*)?$/i', $heading)) {
                    $activeSection = strtolower(preg_replace('/\s*[:\-].*$/', '', $heading));
                    $foundTargetSection = true;
                } else {
                    $activeSection = null;
                }
                continue;
            }

            if ($activeSection !== null) {
                $sectionLines[] = [
                    'line' => $line,
                    'lineNumber' => $lineNumber + 1,
                    'section' => $activeSection,
                ];
            }
        }

        return $foundTargetSection ? $sectionLines : [];
    }

    private function parseRoadmapLine(string $line): ?array {
        $trimmed = trim(strip_tags($line));
        if ($trimmed === '') {
            return null;
        }

        if ($this->isRoadmapNoiseLine($trimmed)) {
            return null;
        }

        if (preg_match('/^\s*[-*]\s*\[( |x|X)\]\s+(.+)$/', $line, $match)) {
            $title = trim(strip_tags((string) $match[2]));
            if ($title === '') {
                return null;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($title);

            return [
                'title' => $title,
                'status' => strtolower(trim((string) $match[1])) === 'x' ? 'done' : 'todo',
                'priority' => $priority,
            ];
        }

        if (preg_match('/^\s*(?:[-*]|\d+\.)\s+(.+)$/', $line, $match)) {
            $title = trim(strip_tags((string) $match[1]));
            if ($title === '' || strlen($title) < 3) {
                return null;
            }

            if (preg_match('/^(#{1,6}|[-=]{3,})\s*$/', $title)) {
                return null;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($title);

            return [
                'title' => $title,
                'status' => 'todo',
                'priority' => $priority,
            ];
        }

        if (preg_match('/^\s*(?:[#>\-\*]\s*)?(?:TODO|TO DO)\s*[:\-]\s*(.+)$/i', $line, $match)) {
            $title = trim(strip_tags((string) $match[1]));
            if ($title === '') {
                return null;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($title);

            return [
                'title' => $title,
                'status' => 'todo',
                'priority' => $priority,
            ];
        }

        // Accept inline TODO markers outside bullet lists, e.g. "Refactor auth TODO: split service"
        if (preg_match('/\b(?:TODO|TO DO)\b\s*[:\-]\s*(.+)$/i', $trimmed, $match)) {
            $title = trim(strip_tags((string) $match[1]));
            if ($title === '') {
                return null;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($title);

            return [
                'title' => $title,
                'status' => 'todo',
                'priority' => $priority,
            ];
        }

        // Accept bracket notation, e.g. "[TODO] improve telemetry card"
        if (preg_match('/^\s*\[\s*(?:TODO|TO DO)\s*\]\s*(.+)$/i', $trimmed, $match)) {
            $title = trim(strip_tags((string) $match[1]));
            if ($title === '') {
                return null;
            }

            [$title, $priority] = $this->extractPriorityAndNormalizeTitle($title);

            return [
                'title' => $title,
                'status' => 'todo',
                'priority' => $priority,
            ];
        }

        return null;
    }

    private function isRoadmapNoiseLine(string $line): bool {
        // Ignore summary/stat lines from generated output, e.g. "0 Packages · 0 Types · 0 Methods · 0 TODOs".
        if (preg_match('/^\**\s*\d+\s+packages?\s*[\-\x{00B7}]\s*\d+\s+types?\s*[\-\x{00B7}]\s*\d+\s+methods?\s*[\-\x{00B7}]\s*\d+\s+todos?\s*\**$/iu', $line)) {
            return true;
        }

        if (preg_match('/^\s*\d+\s+todos?\s*$/i', $line)) {
            return true;
        }

        if (preg_match('/^\s*(?:language|last updated|auto-generated|code overview)\b/i', $line)) {
            return true;
        }

        return false;
    }

    private function extractPriorityAndNormalizeTitle(string $title): array {
        $priority = 'normal';

        if (preg_match('/\[(p1|p2|p3|high|medium|med|low)\]/i', $title, $match)) {
            $token = strtolower((string) $match[1]);
            if ($token === 'p1' || $token === 'high') {
                $priority = 'high';
            } elseif ($token === 'p2' || $token === 'medium' || $token === 'med') {
                $priority = 'medium';
            } elseif ($token === 'p3' || $token === 'low') {
                $priority = 'low';
            }
        }

        $cleanTitle = trim((string) preg_replace('/\[(p1|p2|p3|high|medium|med|low)\]/i', '', $title));
        if ($cleanTitle === '') {
            $cleanTitle = trim($title);
        }

        return [$cleanTitle, $priority];
    }

    private function normalizeRoadmapTitle(string $title): string {
        $normalized = strtolower(trim($title));
        $normalized = (string) preg_replace('/\[(p1|p2|p3|high|medium|med|low)\]/i', '', $normalized);
        $normalized = (string) preg_replace('/\s+/', ' ', $normalized);
        $normalized = (string) preg_replace('/[^a-z0-9 ]/', '', $normalized);
        return trim($normalized);
    }

    private function mergeRoadmapItems(array $existingItems, array $syncedItems): array {
        $merged = $existingItems;
        $indexByTitle = [];

        foreach ($merged as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $key = $this->buildRoadmapMergeKey((array) $item);
            if ($key !== '' && !isset($indexByTitle[$key])) {
                $indexByTitle[$key] = $index;
            }
        }

        foreach ($syncedItems as $synced) {
            $title = trim((string) ($synced['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $key = $this->buildRoadmapMergeKey((array) $synced);
            if ($key !== '' && isset($indexByTitle[$key])) {
                $targetIndex = $indexByTitle[$key];
                $current = (array) $merged[$targetIndex];

                $currentStatus = (string) ($current['status'] ?? 'todo');
                $incomingStatus = (string) ($synced['status'] ?? 'todo');
                $resolvedStatus = ($currentStatus === 'done' && $incomingStatus === 'todo')
                    ? 'done'
                    : $incomingStatus;

                $current['title'] = $synced['title'] ?? ($current['title'] ?? $title);
                $current['status'] = $resolvedStatus;
                $current['priority'] = $synced['priority'] ?? ($current['priority'] ?? 'normal');
                $current['sourceLine'] = $synced['sourceLine'] ?? ($current['sourceLine'] ?? null);
                $current['sourceSection'] = $synced['sourceSection'] ?? ($current['sourceSection'] ?? null);
                $current['syncSource'] = (string) ($synced['syncSource'] ?? 'readmesync');

                $merged[$targetIndex] = $current;
                continue;
            }

            $synced['syncSource'] = (string) ($synced['syncSource'] ?? 'readmesync');
            $merged[] = $synced;
            $newIndex = count($merged) - 1;
            if ($key !== '') {
                $indexByTitle[$key] = $newIndex;
            }
        }

        return $merged;
    }

    private function buildRoadmapMergeKey(array $item): string {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            return '';
        }

        $titleKey = $this->normalizeRoadmapTitle($title);
        if ($titleKey === '') {
            return '';
        }

        $source = strtolower(trim((string) ($item['sourceSection'] ?? '')));
        if ($source === '' || $source === 'roadmap' || $source === 'todo') {
            return $titleKey;
        }

        return $titleKey . '|' . $source;
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

    private function normalizeGitHubRepoUrl(string $url): ?string {
        $parsed = $this->parseGitHubOwnerRepo($url);
        if (!$parsed) {
            return null;
        }

        $owner = trim((string) ($parsed['owner'] ?? ''));
        $repo = trim((string) ($parsed['repo'] ?? ''));
        if ($owner === '' || $repo === '') {
            return null;
        }

        return 'https://github.com/' . rawurlencode($owner) . '/' . rawurlencode($repo);
    }

    private function appendRoadmapTodoIfMissing(array $items, string $title, string $description = '', string $sourceSection = 'ops', string $priority = 'normal'): array {
        $key = $this->normalizeRoadmapTitle($title) . '|' . strtolower(trim($sourceSection));
        if ($key === '|') {
            return $items;
        }

        foreach ($items as $item) {
            $existingTitle = trim((string) ($item['title'] ?? ''));
            $existingSection = strtolower(trim((string) ($item['sourceSection'] ?? '')));
            $existingKey = $this->normalizeRoadmapTitle($existingTitle) . '|' . $existingSection;
            if ($existingKey === $key) {
                return $items;
            }
        }

        $items[] = [
            'id' => 'manual-todo-' . substr(md5($title . '|' . $sourceSection), 0, 10),
            'title' => $title,
            'description' => $description,
            'status' => 'todo',
            'priority' => in_array($priority, ['high', 'medium', 'low', 'normal'], true) ? $priority : 'normal',
            'sourceLine' => null,
            'sourceSection' => $sourceSection,
            'syncSource' => 'manual',
        ];

        return $items;
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

    private function flash(string $type, string $message): void {
        $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
    }

    private function popFlash(?string $type = null): ?array {
        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        if ($type && $flash && $flash['type'] !== $type) return null;
        return $flash;
    }

    private function notFound(): void {
        http_response_code(404);
        $this->renderAdmin('dashboard', ['stats' => [], 'flash' => ['type' => 'error', 'message' => 'Item niet gevonden.']], 'Niet gevonden');
    }

    private function csrfFail(string $redirect): void {
        $this->flash('error', 'Beveiligingstoken ongeldig. Probeer opnieuw.');
        header("Location: {$redirect}");
        exit;
    }
}
