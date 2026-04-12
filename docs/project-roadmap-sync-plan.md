# Project Roadmap Sync Plan

## Doel
Projecten in de portfolio krijgen een automatisch gesynchroniseerde roadmap uit ReadmeSync.API TODO-data,
met multi-image galerijen per project en een centrale roadmap-overzichtspagina.

## Geïmplementeerde flow

1. Admin maakt of bewerkt een project met `repo_url`.
2. Portfolio doet meteen een sync-call naar ReadmeSync.API.
3. TODO-items worden opgeslagen in de DB (`project_roadmap_items` tabel).
4. Bezoeker opent projectdetail en ziet:
   - galerij carousel (meerdere foto's) met dots-navigatie,
   - overzicht tab (beschrijving + features),
   - roadmap tab met TODO-items (filter: open/done/high, GitHub deep links).
5. Bezoeker of admin kan naar centrale pagina (`?page=project-roadmaps`) met:
   - zoekfunctie, progress bars per project, last-sync timestamp.
6. Admin kan alle projecten tegelijk syncen via "Sync roadmaps" knop.
7. Optionele externe cron via `?page=cron-sync-roadmaps&token=SECRET`.

## Basis die is opgezet

### Fase 1 — Multi-image gallery ✅
- `project_images` DB tabel (migrate_v3.sql)
- `ProjectModel::addImage()`, `deleteImage()`, `getImagesByProjectId()`
- `decodeRow($row, $withGallery=true)` mergt cover + gallery images
- Admin create/edit: cover + gallery_images[] multi-upload
- project-detail.php: carousel met dots-navigatie + hide nav bij 1 afbeelding

### Fase 2 — Roadmap JSON → DB ✅
- `project_roadmap_items` + `project_sync_log` tabellen (migrate_v3.sql)
- `ProjectRoadmapModel.php` nieuw model
- `ProjectRoadmapService.php` refactored: schrijft naar DB, JSON als fallback
- `database/migrate_roadmap_data.php` eenmalig migratiescript

### Fase 3 — Enhanced Roadmap UI ✅
- Per-project roadmap tab: filters open/done/high, GitHub deep links (file:line)
- Centrale roadmap pagina: zoekfunctie, progress bars, sync-timestamps
- `getSyncSummary()` in service voor efficiënte bulk-datalaad

### Fase 4 — Bulk sync ✅
- Admin "Sync roadmaps" knop (rate limit: 5 min tussen runs)
- Cron endpoint: `?page=cron-sync-roadmaps&token=CRON_SYNC_TOKEN` (env var)

### Fase 5 — ReadmeSync pagina polish ✅
- Loading spinner + disabled button tijdens API call
- Copy-to-clipboard knop op resultaat
- Quicklinks als cards (betere styling)

## Teststrategie

1. DB tests:
   - `php tests/ProjectRoadmapModelTest.php`
   - `php tests/ProjectRoadmapServiceTest.php`
   - `php tests/ProjectImageTest.php`

2. Handmatige smoke tests:
   - Admin project aanmaken met 3+ afbeeldingen → carousel werkt
   - Project met repo_url aanmaken → auto-sync → roadmap tab toont items
   - Roadmap filters testen (open/done/high)
   - GitHub deep link klikt door naar correct bestand+regel
   - "Sync roadmaps" in admin → alle projecten gesynchroniseerd
   - Cron endpoint met correct/fout token
   - ReadmeSync pagina: loading state, copy knop

## Deployment checklist

1. `database/migrate_v3.sql` runnen op productie (Combell MySQL)
2. (Optioneel) `php database/migrate_roadmap_data.php` voor bestaande JSON-data
3. Env var `CRON_SYNC_TOKEN` instellen als je de cron endpoint wilt gebruiken
4. Bestanden uploaden naar Combell via FTP/SFTP

## Open TODO's (volgende iteraties)

- [ ] `tests/ProjectImageTest.php` schrijven
- [ ] `tests/ProjectRoadmapModelTest.php` schrijven
- [ ] Roadmap diff/versiehistoriek (toon "nieuw sinds vorige sync")
- [ ] Sync retry + exponential backoff bij API-fouten
- [ ] Multi-image sort_order drag-and-drop in admin
- [ ] Volledige i18n-audit: nieuwe labels in translations.php (NL/EN keys)
- [ ] Roadmap status handmatig wijzigen in admin (markeer item als done)

## Risico's

- API latency tijdens admin save flow (max 40s timeout ingesteld)
- Hosting file write-permissions voor JSON-fallback
- Contract drift wanneer API response verandert (contract check in service)
