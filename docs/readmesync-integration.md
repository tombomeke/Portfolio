# ReadmeSync Integration

How the Portfolio connects to **ReadmeSync.API** to pull per-project TODO items into the roadmap UI.

---

## Overview

```
Admin saves project (with repo_url)
  └─ AdminController → ProjectRoadmapService::syncProjectRoadmap()
        └─ POST https://tombomekestudio.com/api/readmesync/generate
              { githubRepoUrl, clientApp, userId, userName }
        └─ Response: { success, language, content, todos[], apiContractVersion }
        └─ ProjectRoadmapModel::upsertFromSync() → project_roadmap_items table
        └─ ProjectRoadmapModel::logSync()        → project_sync_log table
```

The same service is used for:
- Auto-sync on admin project create/update
- Manual per-project sync on the public project-detail page (`?sync=1`)
- Bulk sync via admin button (`?page=admin&section=projects&action=sync-all`)
- External cron trigger (`?page=cron-sync-roadmaps&token=CRON_SYNC_TOKEN`)

---

## API contract

The service checks that the response contains the key `todos`.
If it is missing, the API is running an old runtime or pointing at the wrong directory.

**Required response shape:**
```json
{
  "success": true,
  "language": "php",
  "content": "...",
  "apiContractVersion": "1.x",
  "todos": [
    { "file": "app/Foo.php", "line": 42, "text": "refactor this", "status": "open", "priority": "high" }
  ]
}
```

**Contract check after every ReadmeSync.API deployment:**
1. `GET /health` must return `apiContractVersion`.
2. `POST /api/readmesync/generate` response must contain key `todos`.
3. If `todos` is missing: check ReadmeSync.API GitHub token config and redeploy.

---

## Data flow into DB

`upsertFromSync(projectId, items[], apiContractVersion)`:
1. `DELETE FROM project_roadmap_items WHERE project_id = :id`
2. `INSERT` each item with `last_seen_at = NOW()`

**Known limitation:** Full delete+insert means manually overridden `done` statuses are lost on re-sync.
See `TODO(roadmap): preserve manually-set 'done' status` in `ProjectRoadmapModel.php:78`.

---

## JSON fallback

If the DB tables (`project_roadmap_items`, `project_sync_log`) do not exist yet (i.e. `migrate_v3.sql`
has not been run), the service silently falls back to a JSON file:

```
app/Config/project_roadmaps.json
```

All reads and writes go through this file instead. Once `migrate_v3.sql` is run and the DB tables
exist, the JSON file is no longer consulted and can be deleted.

---

## Cron endpoint

```
GET ?page=cron-sync-roadmaps&token=<CRON_SYNC_TOKEN>
```

- Token is validated against the `CRON_SYNC_TOKEN` environment variable.
- Syncs all projects that have a `repo_url`.
- Returns JSON output (not HTML).
- A 5-minute cooldown is enforced in `AdminController` for the admin bulk-sync button.
- The cron endpoint enforces a server-side minimum interval via site setting `cron_sync_min_interval_seconds` (default 3600).
- Every cron hit is logged to `activity_logs` with action `cron_sync`, visible in admin activity logs.

---

## Debugging

When a sync returns "Repository niet gevonden of is privé":

1. Open the page source and look for `<!-- [ReadmeSync debug]`.
2. Check `http_code` and `curl_error` values in the comment.
3. If `http_code` is 404: the ReadmeSync.API GitHub token is likely expired or misconfigured.
   Fix it in `ReadmeSync.API` (env vars `GitHub:Tokens` / `GitHub__Tokens__*`) and redeploy.

The GitHub token lives in the **ReadmeSync.API repo**, not here.

---

## Related environment variables

| Variable | Required | Description |
|---|---|---|
| `READMESYNC_API_URL` | No (has default) | Full URL to `POST /api/readmesync/generate` |
| `READMESYNC_ADMIN_TELEMETRY_URL` | No (has default) | Admin telemetry endpoint |
| `READMESYNC_ADMIN_TELEMETRY_EXPORT_URL` | No (has default) | Telemetry CSV export URL |
| `READMESYNC_ADMIN_API_KEY` | Yes (for telemetry) | API key sent as `X-API-Key` header |
| `CRON_SYNC_TOKEN` | Yes (for cron) | Secret token to authenticate the cron endpoint |

---

## Related repos

| Repo | Role |
|---|---|
| `tombomeke-ehb/ReadmeSync.API` | ASP.NET Core API on tombomekestudio.com — scans GitHub repos for TODOs |
| `tombomeke-ehb/ReadmeSync` | .NET 8 CLI tool (NuGet) |
