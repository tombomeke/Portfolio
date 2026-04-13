# Portfolio – Claude Context

## Project
Tom Dekoning's live portfolio op **tombomeke.com** (Combell shared hosting, PHP).
Custom PHP MVC — geen framework. Eigenhandig gebouwd.

## Instructies

Werkdiscipline en kwaliteitseisen:

1. Voor je start met coderen:
- Lees eerst bestaande TODO's en relevante documentatie.
- Werk dit bestand (`CLAUDE.md`) bij wanneer een taak of flow inhoudelijk verandert.

2. Taal en duidelijkheid:
- Code en comments zijn in het Engels.
- Documentatie is duidelijk, kort en praktisch.

3. TODO-beleid (verplicht):

**Twee-lagen systeem** — TODOs leven op twee plaatsen met een duidelijke rolverdeling:

| Laag | Locatie | Wanneer gebruiken |
|---|---|---|
| **Code comment** | `// TODO(scope): ...` in het PHP-bestand zelf | Iets staat open op een *specifieke regel* in bestaande code |
| **Project backlog** | Onderstaande lijst in dit bestand | Features die nog niet bestaan, audits over meerdere bestanden, tests, architectuurkeuzes |

**Vuistregel:** is er een concreet bestaand bestand én specifieke plek voor de TODO? Zet het daar én in de backlog. Is het een feature-idee, audit of testbestand dat nog niet bestaat? Dan alleen in de backlog hieronder.

**Waarom twee lagen?** De admin roadmap pagina (`?page=admin&section=projects`) synchroniseert TODO-comments vanuit code via de ReadmeSync API. Die API scant codebestanden — niet dit CLAUDE.md bestand. Code-TODOs komen dus automatisch in de admin UI terecht. Plannings-TODOs zonder code-thuis leven alleen hier.

Aanvullende regels:
- Gebruik altijd het format `TODO(scope): korte uitleg in het Engels`.
- Vink of verwijder TODO's zodra het werk klaar en getest is — zowel in code als in de backlog.
- Laat geen verouderde of vage TODO's achter in beide lagen.

4. Test- en debugbeleid:
- Elke functionele wijziging moet getest worden.
- Voer relevante tests/checks uit voordat een taak als klaar wordt gemarkeerd.
- Als iets niet testbaar is in de huidige omgeving: noteer waarom en geef een veilige fallback of vervolgstap.

5. Productieklare oplevering:
- Geen debugcode in productieflow (`var_dump`, losse `console.log`, tijdelijke testhooks).
- Geen onnodige comments of dode code.
- Houd wijzigingen klein, leesbaar en consistent met bestaande stijl.

6. Definition of Done per taak:
- Implementatie werkt functioneel.
- Relevante tests/checks zijn uitgevoerd en slagen.
- Documentatie en TODO-status zijn bijgewerkt.
- Geen tijdelijke debug/test-restanten in productiecode.

## Structuur
```
index.php                          ← router (?page=xxx)
app/
  Auth/Auth.php                    ← session auth helper (login, logout, CSRF, roles)
  Controllers/
    PortfolioController.php        ← publieke pagina's
    AdminController.php            ← admin panel (dispatch via ?page=admin&section=...)
  Models/
    ActivityLogModel.php           ← admin activity logging
    NewsModel.php                  ← news (DB, R+W)
    NewsCommentModel.php           ← news comments + approval flow
    FaqModel.php                   ← FAQ categories + items (DB, R+W)
    ProjectModels.php              ← projecten (DB, R+W) + gallery (project_images tabel)
    ProjectRoadmapModel.php        ← roadmap items per project (DB, project_roadmap_items + project_sync_log)
    ContactMessageModel.php        ← contact inbox (DB)
    UserModel.php                  ← gebruikers + profielvelden
    TagModel.php                   ← news tags (many-to-many)
    SiteSettingModel.php           ← dynamische site settings
    SkillModel.php                 ← skills (statisch)
    GameStatsModel.php             ← game stats (API cache)
  Views/
    layout.php + *.php             ← publieke views
    admin/layout.php               ← admin layout
    admin/{news,faq,projects,contact,users,comments,tags,settings,activity-logs,profile,dev-life,wip}/  ← admin views
  Config/
    translations.php               ← NL/EN vertaalsysteem
    Database.php                   ← PDO singleton
    db.php                         ← credentials (wel lokaal aanwezig, NIET delen/committen)
public/
  css/style.css, admin.css         ← stylesheets
  images/uploads/{news,projects,avatars}/  ← geüploade afbeeldingen
database/
  migrate.sql                      ← alle CREATE TABLE statements
  migrate_v2.sql                   ← uitbreidingen (tags/comments/activity/settings/profile fields)
  migrate_v3.sql                   ← project_images, project_roadmap_items, project_sync_log
  migrate_roadmap_data.php         ← eenmalig: JSON roadmaps → DB (run na migrate_v3)
  seed_projects.sql                ← initiële projectdata (run na migrate)
  seed_site_settings.sql           ← defaults voor site settings
  seed_skills.sql                  ← skills seed
```

## Routing
Alles via `?page=xxx`. Nieuwe pagina = case in `index.php` + methode in controller + view in `app/Views/`.

WIP-routing is configureerbaar:
- `index.php` leest `app/Config/wip_pages.json`.
- Pagina's in die JSON worden doorgestuurd naar `showWIP(...)`.
- Owner kan dit beheren via `?page=admin&section=wip`.

## Database (Combell MySQL)
Credentials komen nu uit `PORTFOLIO_DB_HOST`, `PORTFOLIO_DB_NAME`, `PORTFOLIO_DB_USER` en `PORTFOLIO_DB_PASS` via `app/Config/db.php`.
Gebruik PDO via `Database::getConnection()`.
Migrations draaien we manueel of via een simpel PHP-script (geen ORM).

`migrate_v2.sql` is aangepast voor MySQL 5.7-compatibiliteit:
- geen `ADD COLUMN IF NOT EXISTS` (geeft syntax error op 5.7),
- losse `ALTER TABLE ... ADD COLUMN ...` statements,
- duplicate-column errors mogen veilig genegeerd worden bij herhaald draaien.

Belangrijk voor profielen/avatars:
- `users.profile_photo_path` bevat een webpad (meestal `public/images/uploads/avatars/<file>`).
- Views moeten dit veld **niet** nogmaals prefixen met `public/images/uploads/avatars/`, anders krijg je een broken image URL.

## Migratie van backend-web-portfolio
**tombomeke-ehb/backend-web-portfolio** is een Laravel 12 app (5x uitgebreider) die we aan het migreren zijn naar déze structuur. Prioriteit:
1. ✅ News systeem (nieuwsberichten, NL/EN)
2. ✅ FAQ systeem
3. ✅ Projects DB-driven (admin CRUD, multi-image gallery, roadmap sync)
4. ✅ Contact berichten opslaan in DB + admin inbox

## Auth & Admin systeem
Session-based auth met twee rollen: `owner` (tombomeke) en `admin` (vertrouwde vrienden).
- Owner kan alles + admins toevoegen/verwijderen
- Admin kan content beheren (news, FAQ, projects, contact)
- Geen publieke registratie — owner maakt admins aan via `?page=admin&section=users`
- Eerste owner-account aanmaken via `?page=setup` (werkt alleen als er nog geen users zijn)

Session payload bevat naast role/username ook profieldata (zoals `profile_photo_path` en `preferred_language`) voor correcte navbar-weergave.

## Gepland voor later (nog niet gebouwd)
- E-mailverificatie voor admins
- Publieke gebruikersprofielen uitbreiden (bio/links/activity)
- Fijnmazige permissies per admin
- Verdere i18n-audit op alle publieke/admin views (geen hardcoded NL strings)

## Bekende aandachtspunten
- Als profielfoto niet zichtbaar is:
  1. check of upload in `public/images/uploads/avatars/` staat,
  2. check of `users.profile_photo_path` is gevuld,
  3. check of sessie is ververst na profiel-update (nodig voor navbar avatar).
- Vertalingen: gebruik overal `trans('key')` i.p.v. hardcoded strings in views (zeker voor empty states).
- Zonder `migrate_v2.sql` blijven profielpagina's bruikbaar door fallback-queries in `UserModel`, maar profielvelden/tags/comments/settings ontbreken functioneel totdat migratie draait.

## ReadmeSync integratie
`?page=readmesync` → cURL call naar `https://tombomekestudio.com/api/readmesync/generate`
Toont live code-overzicht van elke publieke GitHub repo.

Project-roadmap flow (volledig uitgebouwd):
- `?page=project&slug=<slug>` toont projectdetail: cover + galerij carousel, tabs `Overzicht` en `Roadmap`.
- Roadmap tab: filters (open/done/high), GitHub deep links per TODO item, last-sync timestamp.
- `?page=project-roadmaps` toont centrale roadmap-pagina: zoekfunctie, progress bars, alle projecten.
- Bij admin project create/update met `repo_url` wordt automatisch ReadmeSync API aangeroepen.
- TODO-opslag in DB-tabel `project_roadmap_items` via `ProjectRoadmapModel`. JSON-fallback actief zolang migrate_v3 nog niet gedraaid is.
- Admin "Sync roadmaps" knop voor bulk-sync van alle projecten (rate limit 5 min).
- Cron endpoint: `?page=cron-sync-roadmaps&token=CRON_SYNC_TOKEN` (env var vereist).
- Multi-image galerij: `project_images` tabel, admin create/edit ondersteunt meerdere uploads.

Telemetry wordt nu ook server-side opgehaald via de ReadmeSync API-admin endpoint en getoond in `?page=admin&section=telemetry`.
Daarvoor worden `READMESYNC_API_URL`, `READMESYNC_ADMIN_TELEMETRY_URL` en `READMESYNC_ADMIN_API_KEY` uit env gebruikt.

Recente hardening in Portfolio:
- cURL guard op `curl_init`,
- `CURLOPT_CONNECTTIMEOUT` + `CURLOPT_FOLLOWLOCATION`,
- SSL fallback retry op certificate/ssl errors,
- debugvelden naar view: `debugCurlErr`, `debugHttpCode`, `debugRawBody`.

Auth-gedrag:
- Genereren van README vereist login; gasten zien een info-notice met login/register links.

Contract-check na elke ReadmeSync.API deployment (verplicht):
1. `GET https://tombomekestudio.com/` of `GET /health` moet `apiContractVersion` tonen.
2. `POST /api/readmesync/generate` moet response-key `todos` bevatten (naast `success`, `language`, `content`).
3. Als `todos` ontbreekt, draait oude runtime of verkeerde target-map; eerst dat fixen, pas dan Portfolio roadmap sync testen.

Praktische debugflow bij "Repository niet gevonden of is privé":
1. open page source en zoek comment `[ReadmeSync debug]`.
2. check `http_code` en `curl_error`.
3. bij structurele 404 vanuit API: controleer ReadmeSync.API GitHub token configuratie (`GitHub:Tokens` / `GitHub__Tokens__*`) en redeploy API.

Let op: GitHub token staat in de API-repo (`ReadmeSync.API`), niet in deze Portfolio-repo.
Let op: database- en API-secrets staan niet meer hardcoded in de repo; zet ze server-side via env vars.

## Project-roadmap (geïmplementeerd + open TODO's)
Hoofddoel: per project automatisch roadmap TODO's tonen via ReadmeSync, plus centrale roadmap-overzichten.

Geïmplementeerd ✅:
1. ✅ Multi-image galerij (project_images DB, admin carousel, dots-navigatie)
2. ✅ Roadmap DB-opslag (project_roadmap_items, project_sync_log)
3. ✅ Roadmap UI: filters open/done/high, GitHub deep links, last-sync timestamp
4. ✅ Centrale roadmap pagina: zoekfunctie, progress bars
5. ✅ Bulk sync: admin knop + cron endpoint (CRON_SYNC_TOKEN env var)
6. ✅ ReadmeSync pagina: loading spinner, copy-to-clipboard, betere quicklinks

Open TODO's (volgende iteraties):

**Roadmap / ReadmeSync**
- [ ] `TODO(roadmap)`: Roadmap items handmatig markeren als done/open in admin (zonder re-sync)
- [ ] `TODO(roadmap)`: Diff/versiehistoriek per sync — "nieuw vs. verdwenen items" badge → `ProjectRoadmapModel::upsertFromSync()`
- [ ] `TODO(roadmap)`: Preserve manually-set 'done' status across syncs → `ProjectRoadmapModel::upsertFromSync()`
- [ ] `TODO(roadmap)`: Retry met exponential backoff bij API-fouten → `ProjectRoadmapService::syncProjectRoadmap()`
- [ ] `TODO(roadmap)`: Optional "target section" input voor admin roadmap parser → `AdminController.php:~1595`
- [ ] `TODO(roadmap)`: Keep source line numbers in roadmap UI traceability → `AdminController.php:~2374`
- [ ] `TODO(cron)`: Last-run timestamp check om te-frequent cron calls te voorkomen → `PortfolioController::cronSyncRoadmaps()`

**Gallery / Projects**
- [ ] `TODO(gallery)`: Drag-and-drop sort_order reordering voor gallery images in admin edit view
- [ ] `TODO(admin)`: Per-project sync resultaat tonen op sync-all completion page

**Auth / Security**
- [ ] `TODO(auth)`: E-mailverificatie flow voor nieuw aangemaakte admin accounts
- [ ] `TODO(upload)`: Server-side MIME type validatie op image uploads (niet alleen extensie)
- [ ] `TODO(csrf)`: Audit alle admin POST forms — elk form heeft `Auth::csrfField()` + handler verifieert

**Code kwaliteit / architectuur**
- [ ] `TODO(cache)`: `GameStatsModel` raakt externe API bij elke pageload — voeg bestandscache toe (10 min TTL)

**i18n**
- [ ] `TODO(i18n)`: Volledige audit op hardcoded NL strings in views (bekende locaties: `project-detail.php`, `project-roadmaps.php`, admin views)
- [ ] `TODO(i18n)`: Ontbrekende vertaalsleutels toevoegen voor roadmap UI labels (open/done/high filters, sync timestamp, progress)

**Tests**
- [ ] `TODO(test)`: `tests/ProjectImageTest.php` schrijven (gallery CRUD + sort_order)
- [ ] `TODO(test)`: `tests/ProjectRoadmapModelTest.php` schrijven (upsertFromSync, logSync, getLastSync)

Documentatie: `docs/architecture.md`, `docs/readmesync-integration.md`, `docs/database-schema.md`

## Recente terminal geschiedenis (Claude)
- WIP admin-sectie toegevoegd (`admin/wip`) + `wip_pages.json` configuratie.
- Dashboard layout mobiel beter gemaakt (table wrappers, responsive grid-aanpassingen).
- Dashboard roadmap omgezet naar "migratie voltooid" met `roadmap-item--done`.
- Dynamische roadmap toegevoegd via `app/Config/roadmap_items.json` + owner beheerpagina `?page=admin&section=roadmap`.
- Roadmap kan syncen vanuit ReadmeSync-output door markdown checklist mapping (`- [ ]`, `- [x]`) met optie "todos only".
- ReadmeSync guest notice + debug comment in view toegevoegd.
- `migrate_v2.sql` gefixt voor MySQL 5.7 syntax.

## Verwante repos
- `tombomeke-ehb/ReadmeSync.API` — ASP.NET Core .NET 8 API op tombomekestudio.com
  - `POST /api/readmesync/generate` — GitHub repo downloaden + ReadmeSync.Core analyse
  - `POST /api/v1/telemetry/readmesync` — CLI telemetrie (X-API-Key vereist)
- `tombomeke-ehb/ReadmeSync` — .NET 8 CLI tool (NuGet)
- `tombomeke-ehb/backend-web-portfolio` — Laravel bron voor migratie

## Code-stijl
- PHP zonder framework, OOP met simpele klassen
- PDO voor DB, geen query builder
- Views zijn pure PHP partials (geen Blade)
- Vertaalsysteem: `trans('sleutel')` functie uit translations.php
