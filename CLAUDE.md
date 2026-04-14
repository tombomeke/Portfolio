# Portfolio – Claude Context

## Project
Tom Dekoning's live portfolio op **tombomeke.com** (Combell shared hosting, PHP).
Custom PHP MVC — geen framework. Eigenhandig gebouwd.

## Stable facts
- This repo is the live PHP portfolio, not a framework app.
- ReadmeSync roadmaps are generated from code comments plus the admin sync flow.
- `owner` can do everything; `admin` manages content; public registration is disabled.
- Session refresh matters after profile/role changes.
- `users.profile_photo_path` already stores a web path; do not prefix it again in views.
- `CLAUDE.md` is the source of truth for project workflow, open TODOs, and repo-specific guardrails.

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
- De AI doet de commits zelf met duidelijke, compacte commit messages.
- Pas na jouw expliciete akkoord wordt aan het einde een pull request aangemaakt.
- Als de tooling voor pull requests nog niet beschikbaar is, vraag eerst toestemming om de benodigde tool te installeren en gebruik die daarna.

7. Workflow voor grotere taken:
- Start met de bestaande TODO's en relevante context in dit bestand.
- Inspecteer daarna alleen de files die waarschijnlijk de echte bron van het probleem zijn.
- Zet een korte planstap als de wijziging meer dan één file of risico raakt.
- Wijzig zo klein mogelijk, test direct daarna, en werk TODO's bij.
- Commit zelf met een duidelijke, compacte boodschap.
- Vraag pas op het einde om akkoord voor een PR; maak die daarna alleen als jij dat bevestigt.

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

Recent auth hardening:
- `?page=admin` vereist nu expliciet admin/owner (`Auth::requireAdmin()`), niet enkel ingelogd zijn.
- Owner kan bestaande `user`-accounts promoveren naar `admin` en admins terugzetten naar `user` via `?page=admin&section=users`.
- Login hardening: publieke en admin login hebben nu basale session-based rate limiting (tijdvenster) tegen brute-force.
- Redirect hardening: login `redirect` wordt nu gevalideerd als interne URL (`?page=...`) om open redirect te voorkomen.

Migratie hardening:
- `database/migrate_v2.sql` profile-column migratie is nu idempotent via een procedure met `information_schema` checks,
  zodat reruns niet meer falen op duplicate-column errors.

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

## QA Feedback (friend test, April 2026)
Dit is externe feedback uit een gebruikers-test. Sommige punten zijn al opgelost in code, andere moeten nog getriaged of bewust afgewezen worden.

- Login/register kleuren: de koppen en sommige inputvelden mogen visueel rustiger/consistenter worden gemaakt.
- Login validatiefout: invalid email toont nog Nederlands in sommige flows terwijl de site in English staat.
- Admin/profile flow: profielinstellingen moeten niet leiden tot admin-only routes voor non-admins; edit-profile is inmiddels terug naar de publieke/home flow.
- Settings page: layout van checkboxes en buttons oogt nog rommelig; eigen settings-styling kan strakker.
- Settings overlap: settings hebben deels dezelfde profielopties als admin profiel; dubbeling moet nog expliciet gekozen of opgeschoond worden.
- News comments: opmerkingen lijken soms niet direct zichtbaar na posten; comment flow en refresh-gedrag moeten nog geverifieerd worden.
- Projects demo link: portfolio website-project heeft een irrelevante demo-link naar een externe site; mogelijk verwijderen voor dat project.
- Dev Life duplicatie: sommige skills/entries lijken dubbel te staan; data-cleanup of deduping is nodig.
- Dev Life niveau-labels: beschrijvingen zoals HTML/CSS als alleen "advanced" worden als te optimistisch ervaren; inhoudelijke herziening gewenst.
- Contact page translation: enkele teksten vertalen nog niet correct naar English op de contactpagina.
- CV download: downloadknop levert mogelijk een lege PDF; bestand/route controleren.
- Footer mail button: mail-link in de footer lijkt niet te reageren; target/href controleren.
- FAQ content: FAQ toont momenteel "Test"; echte content of verwijdering nodig.
- ReadmeSync label: het groene "Live" label naast de titel voelt optioneel en kan misschien weg.
- Projects imagery: portfolio-project afbeelding zou beter een screenshot van de website moeten zijn in plaats van Minecraft.
- Navigation: Games sectie is nog WIP en kan uit de navigatie zolang die nog niet af is.
- Dev Life sections: Education & Certificates en Current Learning Goals zijn leeg; invullen of verwijderen.
- Account UX: full-name registratie voelt voor sommige gebruikers onnodig; username input kan beter, eventueel met forgot-password en delete-account-data als accounts behouden blijven.
- Preferred language: profieltaal wordt nu te vroeg/automatisch gekozen; bij account-aanmaak of via huidige site-taal vragen kan beter zijn.
- Content framing: News kan eventueel beter als blog/articles worden gepositioneerd.
- Product rationale: de account/community-ideeën zijn bewust gehouden voor comments, nieuws-updates via e-mail en mogelijke forum-uitbreiding later.
- Top-left brand/home link: het portfolio-label linksboven voelt te specifiek; Home als knop is een beter algemeen startpunt.
- Image upload security: profielafbeeldingen verdienen extra audit op file-type validatie, metadata strippen, en mogelijke steganography / payload-risico's.
- All feedback stays captured: ook persoonlijke voorkeuren en suggesties blijven in deze QA-sectie, niet alleen harde bugs.

**Open TODOs from friend test**
- [P2] `TODO(ui)`: Refine auth heading and input contrast on login/register pages.
- [P2] `TODO(auth)`: Replace full-name registration with username-based signup and revisit forgot-password / delete-account-data flows.
- [P2] `TODO(profile)`: Reconcile public settings with admin profile settings so duplicated controls have one clear source of truth.
- [P2] `TODO(news)`: Verify comment post/refresh flow so new comments are visible immediately after submit.
- [P2] `TODO(projects)`: Remove or replace the Portfolio Website demo link and use a real website screenshot instead of Minecraft art.
- [P2] `TODO(dev-life)`: Deduplicate repeated Dev Life entries and trim misleading level labels.
- [P2] `TODO(download)`: Replace the placeholder CV PDF with the real file and verify the download route.
- [P2] `TODO(dev-life)`: Fill or remove the blank Education & Certificates and Current Learning Goals sections.
- [P2] `TODO(content)`: Replace the placeholder FAQ content or remove the FAQ section entirely.
- [x] `TODO(profile)`: Preferred language is now derived from the current site language during signup instead of defaulting to Dutch.
- [P3] `TODO(content)`: Reframe News as blog/articles if that matches the site direction better.

**Opgelost in TODO sweep (April 2026)**
- [x] `TODO(upload)`: Audit profile image uploads for MIME validation — extension now derived from validated MIME map; Uploads::safeDelete() blocks path traversal deletes.
- [x] `TODO(i18n)`: Finish the English audit on the contact page — E-mail/LinkedIn/GitHub headings + PDF suffix use trans() keys.
- [x] `TODO(i18n)`: Replace contact form validation strings with trans() keys.
- [x] `TODO(security)`: Require current-password confirmation before saving sensitive profile settings.
- [x] `TODO(auth)`: Add forgot-password and reset-password flow for user accounts.
- [x] `TODO(config)`: Validate `wip_pages.json` and log malformed JSON instead of silently accepting it.
- [x] `TODO(security)`: Add SRI + crossorigin to external CDN assets in the public layout.
- [x] `TODO(security)`: Add contact form CSRF + anti-spam guard (honeypot + rate limiting).
- [x] `TODO(ui)`: Footer mail button guarded on empty PORTFOLIO_CONTACT_EMAIL env var.
- [x] `TODO(ui)`: Green Live badge removed from ReadmeSync header.
- [x] `TODO(nav)`: Games removed from public nav while section is WIP.
- [x] `TODO(projects)`: Portfolio Website demo_url set to NULL (migrate_v4.sql for prod); old link was stale placeholder.

**Al opgelost / eerder afgerond**
- Admin dashboard/admin route hardening en role checks.
- Taalwissel in admin/publieke shell.
- Custom confirm modal-flash in admin/settings.
- Admin users teller op owner+admin.
- Settings en publieke skills/profile basis.
- Top-left brand link omgezet naar een Home-knop.

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

**Recent afgerond ✅**
- [x] `TODO(auth)`: Admin route hardening — `?page=admin` vereist nu admin/owner (`Auth::requireAdmin()` + extra guard in `index.php`)
- [x] `TODO(auth)`: Owner kan bestaande users promoten naar admin en admins degraderen naar user (`?page=admin&section=users`)
- [x] `TODO(security)`: Login redirect hardening — alleen interne `?page=...` redirects toegestaan
- [x] `TODO(security)`: Basale login rate limiting toegevoegd op publieke en admin login
- [x] `TODO(security)`: Session cookie flags afgedwongen (`httponly`, `secure`, `samesite`) in bootstrap/controller
- [x] `TODO(db)`: `migrate_v2.sql` idempotent gemaakt + schema reconcile voor bestaande `readmesync_scan_logs`
- [x] `TODO(ui)`: Admin login UI/CSS verbeterd voor leesbaarheid en responsive layout
- [x] `TODO(ui)`: Comment author names in news-item linken nu naar publieke profielen (`?page=profile&u=<username>`)
- [x] `TODO(ui)`: Usernames in admin users tabel linken nu naar publieke profielen
- [x] `TODO(ui)`: Alerts/flash meldingen gestandaardiseerd via eigen CSS + custom admin confirm modal (i.p.v. browser confirm)
- [x] `TODO(profile)`: Basis user settings pagina opnieuw toegevoegd (`?page=settings`) met voorkeurstaal + public profile toggle
- [x] `TODO(profile)`: Basis user skills toegevoegd (DB `user_skills`, add/remove in settings, zichtbaar op publiek profiel)
- [x] `TODO(profile)`: User skills bewerken (edit/update flow) toegevoegd op `?page=settings`
- [x] `TODO(profile)`: Extra settings-features uit legacy app gemigreerd (e-mail notificatievoorkeur)
- [x] `TODO(profile)`: Verdere settings parity-check gedaan met legacy app; ontbrekende of irrelevante opties opgeschoond/gedocumenteerd
- [x] `TODO(ui)`: Admin profile form controls verbeterd voor `date`/`file` inputs (eigen styling)
- [x] `TODO(ui)`: Snelle admin-link toegevoegd naar eigen publieke profiel (sidebar account sectie)

**Roadmap / ReadmeSync**
- [x] `TODO(roadmap)`: Roadmap items handmatig markeren als done/open in admin — `?page=admin&section=project-roadmap-items&project_id=N`
- [x] `TODO(roadmap)`: Diff counters (new/kept/removed) per sync — returned by `ProjectRoadmapModel::upsertFromSync()`
- [x] `TODO(roadmap)`: Preserve manually-set 'done' status across syncs — implemented in `upsertFromSync()`
- [x] `TODO(roadmap)`: Retry met exponential backoff bij API-fouten — 3 attempts, 0/400/1200ms, bails on 4xx
- [P3] `TODO(roadmap)`: Optional "target section" input voor admin roadmap parser → `AdminController.php:~1595`
- [P3] `TODO(roadmap)`: Keep source line numbers in roadmap UI traceability → `AdminController.php:~2374`
- [x] `TODO(cron)`: Last-run guard in `cronSyncRoadmaps()` — interval now configurable via admin setting `cron_sync_min_interval_seconds`
- [x] `TODO(cron)`: Cron trigger logging toegevoegd in `activity_logs` (action `cron_sync`, with run_id/status/counts/errors properties)

**Migratiekansen uit backend-web-portfolio**
- [x] `TODO(settings)`: `contact_form_enabled` gate in `showContact()` + `handleContact()`; disabled-state block in contact.php
- [x] `TODO(ops)`: `maintenance_mode` gate in `index.php` → renders `app/Views/maintenance.php` (503); admins bypass
- [x] `TODO(security)`: Voeg current-password confirm stap toe voor gevoelige settings-updates in `PortfolioController::handleSettings()` — `UserModel::verifyPassword()` now available
- [x] `TODO(auth)`: Implementeer token-based forgot-password flow (`password_reset_tokens`) voor self-service account recovery
- [P3] `TODO(profile)`: Voeg `users.timezone` veld + settings input toe en gebruik het voor toekomstige tijdsweergave
- [P3] `TODO(email)`: Migreer admin contact replies van plain-text `mail()` naar herbruikbare HTML e-mail templates
- [P3] `TODO(i18n)`: Overweeg SetLocale-achtige centrale locale resolver i.p.v. verspreide taalchecks in controllers/views

**Gallery / Projects**
- [P2] `TODO(gallery)`: Drag-and-drop sort_order reordering voor gallery images in admin edit view
- [P3] `TODO(admin)`: Per-project sync resultaat tonen op sync-all completion page

**Auth / Security**
- [P3] `TODO(auth)`: E-mailverificatie flow voor nieuw aangemaakte admin accounts
- [x] `TODO(upload)`: Server-side MIME type validatie op image uploads — `handleImageUpload()` derives extension from MIME map; `Uploads::safeDelete()` guards unlink
- [x] `TODO(csrf)`: Audit alle admin POST forms — setup.php gap fixed; all other forms audited and compliant
- [x] `TODO(csrf)`: Rotate CSRF token na succesvolle gevoelige POST-acties — `Auth::rotateCsrf()` added; wired into admin password change + settings POST
- [x] `TODO(security)`: Valideer `realpath()` bij `unlink()` — `Uploads::safeDelete()` in `app/Support/Uploads.php`
- [x] `TODO(security)`: Voeg SRI (`integrity` + `crossorigin`) toe voor externe CDN assets in publieke layout

**Code kwaliteit / architectuur**
- [x] `TODO(cache)`: `GameStatsModel` probeert nu live API-data (`MINECRAFT_SERVER_IP`, `R6_USERNAME`) met 10 min bestandscache en veilige mock fallback
- [x] `TODO(config)`: Valideer `wip_pages.json` schema en log parsefouten i.p.v. stil fallbackgedrag
- [x] `TODO(performance)`: News tags N+1 fixed — `getTagsForItems()` batch loads tags in one IN query

**i18n**
- [P3] `TODO(i18n)`: Volledige audit op hardcoded NL strings in views (bekende locaties: `project-detail.php`, `project-roadmaps.php`, admin views)
- [P3] `TODO(i18n)`: Ontbrekende vertaalsleutels toevoegen voor roadmap UI labels (open/done/high filters, sync timestamp, progress)
- [x] `TODO(i18n)`: Vervang hardcoded NL validatie- en foutmeldingen in `PortfolioController::handleContact()` door `trans()` keys

**Tests**
- [P2] `TODO(test)`: `tests/ProjectImageTest.php` schrijven (gallery CRUD + sort_order)
- [P2] `TODO(test)`: `tests/ProjectRoadmapModelTest.php` schrijven (upsertFromSync, logSync, getLastSync)
- [P2] `TODO(test)`: Breid upload tests uit voor mismatched extension vs MIME in `AdminControllerUploadTest.php`
- [P2] `TODO(test)`: Voeg retry/backoff tests toe voor `ProjectRoadmapService::syncProjectRoadmap()`

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
