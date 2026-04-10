# Portfolio – Claude Context

## Project
Tom Dekoning's live portfolio op **tombomeke.com** (Combell shared hosting, PHP).
Custom PHP MVC — geen framework. Eigenhandig gebouwd.

## Structuur
```
index.php                  ← router (?page=xxx)
app/
  Controllers/PortfolioController.php  ← enige controller
  Models/         ← data classes (deels statisch, deels DB)
  Views/          ← PHP partials, worden via render() in layout.php geladen
  Config/translations.php  ← NL/EN vertaalsysteem
public/
  css/            ← style.css + deelbestanden
  images/, js/
```

## Routing
Alles via `?page=xxx`. Nieuwe pagina = case in `index.php` + methode in controller + view in `app/Views/`.

## Database (Combell MySQL)
Credentials staan in `app/Config/db.php` (niet in git). Gebruik PDO via `Database::getConnection()`.
Migrations draaien we manueel of via een simpel PHP-script (geen ORM).

## Migratie van backend-web-portfolio
**tombomeke-ehb/backend-web-portfolio** is een Laravel 12 app (5x uitgebreider) die we aan het migreren zijn naar déze structuur. Prioriteit:
1. News systeem (nieuwsberichten, NL/EN) ← bezig
2. FAQ systeem
3. Projects DB-driven (nu nog statische array in ProjectModels.php)
4. Contact berichten opslaan in DB (nu alleen e-mail)

Geen user-auth, geen admin dashboard gepland (te complex voor nu).

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
