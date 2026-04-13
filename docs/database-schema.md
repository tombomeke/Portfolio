# Database Schema Reference

Combell MySQL (tested on MySQL 5.7+). Credentials via env vars — see `app/Config/db.php`.

All migrations are run manually. Run them in order.

---

## Migration files

| File | When to run |
|---|---|
| `database/migrate.sql` | Fresh install — creates all core tables |
| `database/migrate_v2.sql` | After migrate.sql — adds profile fields, tags, comments, activity log, site settings |
| `database/migrate_v3.sql` | After migrate_v2.sql — adds gallery and roadmap tables |
| `database/migrate_roadmap_data.php` | One-time — run after migrate_v3.sql to import existing JSON roadmap data |

Seed files (run after migrations):
- `database/seed_projects.sql` — initial project rows
- `database/seed_site_settings.sql` — default site settings
- `database/seed_skills.sql` — skills data

---

## Tables

### `users`
Auth users with profile fields.

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `username` | VARCHAR | Unique |
| `email` | VARCHAR | Unique |
| `password` | VARCHAR | bcrypt hash |
| `role` | ENUM | `owner` or `admin` |
| `birthday` | DATE | Optional |
| `about` | TEXT | Profile bio |
| `public_profile` | TINYINT | 1 = visible, 0 = hidden |
| `preferred_language` | VARCHAR | `nl` or `en` |
| `profile_photo_path` | VARCHAR | Web path, e.g. `public/images/uploads/avatars/foo.jpg` |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

> **Note:** `profile_photo_path` is already a web path. Views must NOT add a prefix.

---

### `projects`
Core project data (language-neutral fields).

| Column | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `slug` | VARCHAR | URL slug, unique |
| `category` | VARCHAR | `web`, `minecraft`, `api`, `cli`, etc. |
| `status` | VARCHAR | `active`, `wip`, `archived`, or NULL |
| `image_path` | VARCHAR | Cover image web path |
| `repo_url` | VARCHAR | GitHub repo URL for ReadmeSync sync |
| `demo_url` | VARCHAR | Live demo URL |
| `tech` | JSON | Array of tech stack labels |
| `sort_order` | INT | Controls display order |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |

### `project_translations`
Bilingual title/description per project.

| Column | Notes |
|---|---|
| `project_id` | FK → `projects.id` |
| `lang` | `nl` or `en` |
| `title` | |
| `description` | Short description |
| `long_description` | Full markdown-ish body |
| `features` | JSON array of feature strings |

---

### `project_images`
Gallery images per project (multi-image carousel).

| Column | Notes |
|---|---|
| `id` | INT PK |
| `project_id` | FK → `projects.id` |
| `image_path` | Web path |
| `caption` | Optional caption |
| `sort_order` | Display order (ASC) |

---

### `project_roadmap_items`
TODO items for each project, populated by ReadmeSync sync.

| Column | Notes |
|---|---|
| `id` | INT PK |
| `project_id` | FK → `projects.id` |
| `file` | Source file path (relative to repo root) |
| `line` | Line number in source file |
| `text` | TODO text content |
| `status` | `open` or `done` |
| `priority` | `normal` or `high` |
| `last_seen_at` | Timestamp of last sync that contained this item |
| `api_contract_version` | Contract version from ReadmeSync API response |

> Items are fully replaced on each sync (DELETE + INSERT). Manual status overrides are lost.
> See `TODO(roadmap)` in `ProjectRoadmapModel.php`.

### `project_sync_log`
One row per sync attempt (success or failure).

| Column | Notes |
|---|---|
| `id` | INT PK |
| `project_id` | FK → `projects.id` |
| `item_count` | Number of TODO items found |
| `api_contract_version` | From API response |
| `success` | TINYINT 0/1 |
| `error_message` | NULL on success |
| `created_at` | Sync timestamp |

---

### `news` / `news_translations`
News posts with NL/EN translations. Same pattern as projects.

### `news_comments`
Comments on news items. Go through an approval flow before being shown publicly.
Fields: `news_id`, `author_name`, `content`, `approved` (0/1), `created_at`.

### `tags` / `news_tag`
Many-to-many tags for news posts.

### `faq_categories` / `faq_items`
FAQ grouped by category. Both tables have `lang` column for bilingual content.

### `contact_messages`
Inbound contact form submissions. Fields: `name`, `email`, `subject`, `message`, `read` (0/1), `created_at`.

### `activity_log`
Admin action log. Fields: `user_id`, `action`, `description`, `created_at`.

### `site_settings`
Key/value store for dynamic settings. Managed via `?page=admin&section=settings`.

### `readmesync_scan_log`
Telemetry log for ReadmeSync scans (populated via API, shown in admin telemetry section).
