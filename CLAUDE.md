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
    NewsModel.php                  ← news (DB, R+W)
    FaqModel.php                   ← FAQ categories + items (DB, R+W)
    ProjectModels.php              ← projecten (DB, R+W — was statische array)
    ContactMessageModel.php        ← contact inbox (DB)
    UserModel.php                  ← gebruikers (owner/admin)
    SkillModel.php                 ← skills (statisch)
    GameStatsModel.php             ← game stats (API cache)
  Views/
    layout.php + *.php             ← publieke views
    admin/layout.php               ← admin layout
    admin/{news,faq,projects,contact,users}/  ← admin views
  Config/
    translations.php               ← NL/EN vertaalsysteem
    Database.php                   ← PDO singleton
    db.php                         ← credentials (niet in git)
public/
  css/style.css, admin.css         ← stylesheets
  images/uploads/{news,projects}/  ← geüploade afbeeldingen
database/
  migrate.sql                      ← alle CREATE TABLE statements
  seed_projects.sql                ← initiële projectdata (run na migrate)
```

## Routing
Alles via `?page=xxx`. Nieuwe pagina = case in `index.php` + methode in controller + view in `app/Views/`.

## Database (Combell MySQL)
Credentials staan in `app/Config/db.php` (niet in git). Gebruik PDO via `Database::getConnection()`.
Migrations draaien we manueel of via een simpel PHP-script (geen ORM).

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

## Gepland voor later (nog niet gebouwd)
- News comments + moderatie
- Tag-systeem voor nieuws (many-to-many)
- Activity logs (admin acties bijhouden)
- E-mailverificatie voor admins
- Publieke gebruikersprofielen
- Site settings (dynamische configuratie via DB)

## ReadmeSync integratie
`?page=readmesync` → cURL call naar `https://tombomekestudio.com/api/readmesync/generate`
Toont live code-overzicht van elke publieke GitHub repo.

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
