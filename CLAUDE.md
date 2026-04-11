# Portfolio – Claude Context

## Project
Tom Dekoning's live portfolio op **tombomeke.com** (Combell shared hosting, PHP).
Custom PHP MVC — geen framework. Eigenhandig gebouwd.

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
    ProjectModels.php              ← projecten (DB, R+W — was statische array)
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
3. Projects DB-driven (nu nog statische array in ProjectModels.php)
4. Contact berichten opslaan in DB (nu alleen e-mail)

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

Telemetry wordt nu ook server-side opgehaald via de ReadmeSync API-admin endpoint en getoond in `?page=admin&section=telemetry`.
Daarvoor worden `READMESYNC_API_URL`, `READMESYNC_ADMIN_TELEMETRY_URL` en `READMESYNC_ADMIN_API_KEY` uit env gebruikt.

Recente hardening in Portfolio:
- cURL guard op `curl_init`,
- `CURLOPT_CONNECTTIMEOUT` + `CURLOPT_FOLLOWLOCATION`,
- SSL fallback retry op certificate/ssl errors,
- debugvelden naar view: `debugCurlErr`, `debugHttpCode`, `debugRawBody`.

Auth-gedrag:
- Genereren van README vereist login; gasten zien een info-notice met login/register links.

Praktische debugflow bij "Repository niet gevonden of is privé":
1. open page source en zoek comment `[ReadmeSync debug]`.
2. check `http_code` en `curl_error`.
3. bij structurele 404 vanuit API: controleer ReadmeSync.API GitHub token configuratie (`GitHub:Tokens` / `GitHub__Tokens__*`) en redeploy API.

Let op: GitHub token staat in de API-repo (`ReadmeSync.API`), niet in deze Portfolio-repo.
Let op: database- en API-secrets staan niet meer hardcoded in de repo; zet ze server-side via env vars.

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
