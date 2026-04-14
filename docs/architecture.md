# Portfolio – Architecture Overview

Custom PHP MVC portfolio for tombomeke.com (Combell shared hosting).
No framework — hand-built routing, controller, model, and view layer.

---

## Entry point & routing

```
GET/POST ?page=xxx
         └─ index.php
               ├─ page=admin|setup  → AdminController::dispatch()
               └─ everything else   → PortfolioController::<method>()
```

`index.php` is the single front-controller. It reads `$_GET['page']` and dispatches:

- `admin`/`setup` → `app/Controllers/AdminController.php`
- everything else → `app/Controllers/PortfolioController.php`

WIP pages are driven by `app/Config/wip_pages.json` (managed via `?page=admin&section=wip`).
Pages listed there are routed to `showWIP()` before the main switch, so no code change is needed
to temporarily disable a page.

---

## Controllers

### `PortfolioController`
Handles all public-facing pages. Each page has a dedicated `show*()` method.
Uses `$this->render($view, $data)` to include `app/Views/layout.php`, which wraps the view partial.

### `AdminController`
Dispatches based on `$_GET['section']` and `$_GET['action']`. Pattern:

```
?page=admin&section=projects&action=edit&id=5
         dispatch() → handleProjects() → renderAdminEdit()
```

`dispatch()` also enforces `Auth::requireAuth()` for every admin section.
`requireOwner()` is called for owner-only sections (users, settings, wip, roadmap).

---

## Models

All models use `Database::getConnection()` (PDO singleton, `FETCH_ASSOC`).
No ORM — plain `prepare()/execute()` with named parameters.

| Model | Table(s) | Notes |
|---|---|---|
| `ProjectModel` | `projects`, `project_translations`, `project_images` | Bilingual via JOIN on lang |
| `ProjectRoadmapModel` | `project_roadmap_items`, `project_sync_log` | Per-project TODO items from ReadmeSync |
| `NewsModel` | `news`, `news_translations` | Bilingual news posts |
| `NewsCommentModel` | `news_comments` | Comments with approval flow |
| `FaqModel` | `faq_categories`, `faq_items` | Grouped FAQ with translations |
| `ContactMessageModel` | `contact_messages` | Inbound contact form submissions |
| `UserModel` | `users` | Auth users, profile fields, avatar |
| `TagModel` | `tags`, `news_tag` | Many-to-many tags for news |
| `SiteSettingModel` | `site_settings` | Key/value dynamic settings |
| `SkillModel` | `skills`, `education`, `learning_goals` | Dev-life page data |
| `ActivityLogModel` | `activity_log` | Admin action log |
| `GameStatsModel` | — | External API (Minecraft stats), cached in session |
| `ReadmeSyncScanLogModel` | `readmesync_scan_log` | Telemetry scan log |

---

## Views

Views are plain PHP partials. The layout wraps each view:

```
app/Views/layout.php        ← public navbar + footer, includes the view partial
app/Views/admin/layout.php  ← admin sidebar + topbar, includes the admin view partial
```

`render($view, $data)` in PortfolioController does `extract($data)` then `require layout.php`,
which in turn includes the view file. No templating engine.

---

## Auth & RBAC

`app/Auth/Auth.php` — static helper class around `$_SESSION['auth_user']`.

Roles:
- `owner` — full access, can manage users and settings
- `admin` — content management (news, FAQ, projects, contact)

Relevant methods:
- `Auth::check()` — is there a logged-in user?
- `Auth::isOwner()` — is the current user the owner?
- `Auth::isAdmin()` — is the user owner OR admin?
- `Auth::requireAuth()` / `requireOwner()` — redirect guard
- `Auth::csrfToken()` / `verifyCsrf()` / `csrfField()` — CSRF helpers

CSRF tokens are stored in `$_SESSION['csrf_token']` and verified on every POST.
Session is regenerated on login and logout to prevent fixation attacks.

---

## Translation system

`app/Config/translations.php` — `Translations` class + `trans()` helper.

```php
trans('projects_title')       // returns NL or EN string depending on session lang
Translations::getCurrentLang() // 'nl' | 'en'
```

Language is stored in `$_SESSION['lang']`. Toggle via `?page=set-lang&lang=en`.
Views should always use `trans('key')` — never hardcode NL strings.

Known gap: not all views use `trans()` yet. Hardcoded strings remain in several views.
See TODO list below.

---

## Environment variables

`app/Config/db.php` loads DB credentials from env vars:

```
PORTFOLIO_DB_HOST
PORTFOLIO_DB_NAME
PORTFOLIO_DB_USER
PORTFOLIO_DB_PASS
```

`portfolioEnv()` lives in `app/Config/env.php` and resolves env vars with a fallback chain:
`getenv()` → `$_ENV` → `$_SERVER` → `.env` file (parsed manually, no library).

Other env vars used at runtime:

| Variable | Used by | Purpose |
|---|---|---|
| `READMESYNC_API_URL` | `ProjectRoadmapService`, `AdminController` | ReadmeSync API endpoint |
| `READMESYNC_ADMIN_TELEMETRY_URL` | `AdminController` | Admin telemetry endpoint |
| `READMESYNC_ADMIN_TELEMETRY_EXPORT_URL` | `AdminController` | Telemetry CSV export |
| `READMESYNC_ADMIN_API_KEY` | `AdminController` | Auth key for telemetry API |
| `PORTFOLIO_CONTACT_EMAIL` | `PortfolioController`, `AdminController`, public layout/contact views | Public contact email address |
| `CRON_SYNC_TOKEN` | `PortfolioController::cronSyncRoadmaps()` | Token for external cron trigger |

---

## Database migrations

All migrations are run manually (no migration runner):

| File | Purpose |
|---|---|
| `database/migrate.sql` | Initial schema (all core tables) |
| `database/migrate_v2.sql` | Profile fields, tags, comments, activity log, site settings |
| `database/migrate_v3.sql` | `project_images`, `project_roadmap_items`, `project_sync_log` |
| `database/migrate_roadmap_data.php` | One-time script: migrate JSON roadmap data → DB |

`migrate_v2.sql` is MySQL 5.7 compatible (no `ADD COLUMN IF NOT EXISTS`).
Duplicate column errors on re-run can be safely ignored.

---

## ReadmeSync integration

See `docs/readmesync-integration.md` for the full integration guide.

Short version:
- `ProjectRoadmapService::syncProjectRoadmap()` calls the API and stores TODO items in DB.
- JSON file (`app/Config/project_roadmaps.json`) is used as a fallback if DB tables don't exist.
- Auto-sync runs when a project with `repo_url` is saved in admin.
- Bulk sync: `?page=admin&section=projects&action=sync-all` (rate-limited to once per 5 min).
- Cron: `?page=cron-sync-roadmaps&token=CRON_SYNC_TOKEN`.
- Cron interval is configured via site setting `cron_sync_min_interval_seconds` in admin settings.
- Cron run events are written to `activity_logs` (action `cron_sync`) and visible in the admin activity log view.

---

## Open TODOs

These are architectural/cross-cutting TODOs tracked here in addition to inline code comments.

### Security & robustness
- [ ] `TODO(cron)`: add per-IP rate limiting or request-frequency guard on the cron endpoint (currently only protected by a static token)
- [ ] `TODO(csrf)`: audit all admin POST forms — verify every form has `Auth::csrfField()` and every handler calls `Auth::verifyCsrf()`
- [ ] `TODO(upload)`: validate MIME type server-side on image uploads (not just extension check)

### Performance
- [ ] `TODO(n+1)`: `ProjectRoadmapService::getSyncSummary()` runs 4 queries per project — replace with a single aggregated SQL query grouped by `project_id`
- [ ] `TODO(cache)`: GameStatsModel hits an external API on every page load — add a short-lived file cache (e.g. 10 min TTL) to reduce latency

### Developer experience
- [ ] `TODO(i18n)`: full audit of views for hardcoded NL strings (known locations: `project-detail.php`, `project-roadmaps.php`, admin views)
- [ ] `TODO(i18n)`: add missing translation keys for roadmap UI labels (open/done/high filters, sync timestamp, progress)

### Features
- [ ] `TODO(roadmap)`: preserve manually-set 'done' status across syncs (see `ProjectRoadmapModel::upsertFromSync()`)
- [ ] `TODO(roadmap)`: diff/version history per sync — surface "new since last sync" badge
- [ ] `TODO(roadmap)`: retry with exponential backoff when `callApi()` returns non-200
- [ ] `TODO(gallery)`: drag-and-drop `sort_order` reordering for gallery images in admin edit view
- [ ] `TODO(admin)`: show per-project sync result breakdown on `sync-all` completion page
- [ ] `TODO(auth)`: email verification flow for newly created admin accounts

### Tests
- [ ] `TODO(test)`: write `tests/ProjectImageTest.php` (gallery CRUD + sort_order)
- [ ] `TODO(test)`: write `tests/ProjectRoadmapModelTest.php` (upsertFromSync, logSync, getLastSync)
