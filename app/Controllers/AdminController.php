<?php
// app/Controllers/AdminController.php

require_once __DIR__ . '/../Auth/Auth.php';
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
    private string              $contactEmail = 'tom1dekoning@gmail.com';
    private string              $readmeSyncApiUrl = 'https://tombomekestudio.com/api/readmesync/generate';

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

        Auth::requireAuth();

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
            } elseif (!preg_match('/^[a-z0-9_]{3,30}$/i', $username)) {
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

        if (Auth::login($username, $password)) {
            ActivityLogModel::log('login', "User '{$username}' logged in");
            header('Location: ?page=admin');
            exit;
        }

        $this->flash('error', 'Invalid username or password.');
        header('Location: ?page=admin&section=login');
        exit;
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
                'users'            => $this->users->count(),
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
                ['news','faq_categories','faq_items','projects','messages','unread_messages','users','skills','education','goals'], 0
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

        $id = $this->news->create([
            'image_path'   => $imagePath,
            'published_at' => $post['published_at'] ?? null,
            'title_nl'     => trim($post['title_nl']),
            'title_en'     => trim($post['title_en']),
            'content_nl'   => trim($post['content_nl']),
            'content_en'   => trim($post['content_en']),
        ]);

        $tagIds = array_filter(array_map('intval', (array)($post['tags'] ?? [])));
        $this->tags->syncForNewsItem($id, $tagIds);

        ActivityLogModel::log('created', "Created news: " . trim($post['title_nl']), 'news_items', $id);
        $this->flash('success', 'Nieuwsbericht aangemaakt.');
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

        $this->news->update($id, [
            'image_path'   => $imagePath,
            'published_at' => $post['published_at'] ?? null,
            'title_nl'     => trim($post['title_nl']),
            'title_en'     => trim($post['title_en']),
            'content_nl'   => trim($post['content_nl']),
            'content_en'   => trim($post['content_en']),
        ]);

        $tagIds = array_filter(array_map('intval', (array)($post['tags'] ?? [])));
        $this->tags->syncForNewsItem($id, $tagIds);

        ActivityLogModel::log('updated', "Updated news: " . trim($post['title_nl']), 'news_items', $id);
        $this->flash('success', 'Nieuwsbericht bijgewerkt.');
        header('Location: ?page=admin&section=news');
        exit;
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
            default:
                $this->listProjects();
        }
    }

    private function listProjects(): void {
        $projects = $this->projects->getAllForAdmin();
        $flash    = $this->popFlash();
        $this->renderAdmin('projects/index', compact('projects', 'flash'), 'Projecten beheren');
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

        $this->projects->create([
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

        $this->flash('success', 'Project aangemaakt.');
        header('Location: ?page=admin&section=projects');
        exit;
    }

    private function editProject(?int $id): void {
        $project = $id ? $this->projects->getByIdForAdmin($id) : null;
        if (!$project) { $this->notFound(); return; }
        $flash = $this->popFlash();
        $this->renderAdmin('projects/edit', compact('project', 'flash'), 'Project bewerken');
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

        $this->flash('success', 'Project bijgewerkt.');
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
        if (!preg_match('/^[a-z0-9_]{3,30}$/i', $username)) $errors[] = 'Ongeldige gebruikersnaam (3–30 tekens, letters/cijfers/_).';
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

    private function handleImageUpload(?array $file, string $subfolder): ?string {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
            return null;
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed, true)) {
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

    private function parseTech(string $raw): array {
        return array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $raw))));
    }

    private function parseFeatures(string $raw): array {
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
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

            if ($action === 'sync') {
                $repoUrl = trim($_POST['repo_url'] ?? '');
                $todosOnly = isset($_POST['todos_only']) && $_POST['todos_only'] === '1';

                // TODO(roadmap): add optional "target section" input so parsing can focus on a single README block (e.g. Roadmap/TODO).

                if ($repoUrl === '' || !preg_match('#^https?://github\.com/[^/]+/[^/]+#i', $repoUrl)) {
                    $this->flash('error', 'Geef een geldige GitHub repo URL op.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $apiError = null;
                $content = $this->fetchReadmeSyncContent($repoUrl, $apiError);
                if ($content === null) {
                    $this->flash('error', $apiError ?: 'ReadmeSync synchronisatie mislukt.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                $items = $this->parseChecklistItems($content, $todosOnly);
                if (empty($items)) {
                    $this->flash('error', 'Geen checklist-items gevonden in de ReadmeSync output.');
                    header('Location: ?page=admin&section=roadmap'); exit;
                }

                // TODO(roadmap): support merge mode (keep manual items + upsert synced items by normalized title).

                $config['items'] = $items;
                $config['repoUrl'] = $repoUrl;
                $config['source'] = 'readmesync';
                $config['lastSyncAt'] = date('c');
                $this->saveRoadmapConfig($config);

                ActivityLogModel::log('updated', 'Synced roadmap from ReadmeSync (' . count($items) . ' items)');
                $this->flash('success', 'Roadmap gesynchroniseerd vanuit ReadmeSync.');
                header('Location: ?page=admin&section=roadmap'); exit;
            }

            if ($action === 'reset') {
                $config = [
                    'source' => 'manual',
                    'repoUrl' => '',
                    'lastSyncAt' => null,
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

    private function roadmapConfigPath(): string {
        return __DIR__ . '/../../app/Config/roadmap_items.json';
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

    private function fetchReadmeSyncContent(string $repoUrl, ?string &$error): ?string {
        $error = null;
        if (!function_exists('curl_init')) {
            $error = 'cURL niet beschikbaar op server.';
            return null;
        }

        $payload = json_encode(['githubRepoUrl' => $repoUrl]);
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

        if ($curlErr) {
            $error = 'ReadmeSync niet bereikbaar: ' . $curlErr;
            return null;
        }
        if ($httpCode !== 200) {
            $error = 'ReadmeSync gaf HTTP ' . $httpCode . '.';
            return null;
        }

        $decoded = json_decode((string) $raw, true);
        $content = trim((string) ($decoded['content'] ?? ''));
        if ($content === '') {
            $error = 'ReadmeSync response bevat geen content.';
            return null;
        }
        return $content;
    }

    private function parseChecklistItems(string $markdown, bool $todosOnly): array {
        preg_match_all('/^\s*[-*]\s*\[( |x|X)\]\s+(.+)$/m', $markdown, $matches, PREG_SET_ORDER);
        $items = [];

        // TODO(roadmap): enrich parsing with priority labels and owner tags (e.g. [P1], @owner).
        // TODO(roadmap): keep source line numbers to improve traceability in roadmap UI.

        foreach ($matches as $index => $match) {
            $status = strtolower(trim($match[1])) === 'x' ? 'done' : 'todo';
            if ($todosOnly && $status === 'done') {
                continue;
            }
            $title = trim(strip_tags($match[2]));
            if ($title === '') {
                continue;
            }
            $items[] = [
                'id' => 'synced-' . ($index + 1) . '-' . substr(md5($title), 0, 8),
                'title' => $title,
                'description' => '',
                'status' => $status,
            ];
        }

        return $items;
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
