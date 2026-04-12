<?php
$summary = is_array($summary ?? null) ? $summary : [];
$items = is_array($items ?? null) ? $items : [];
$telemetry = is_array($telemetry ?? null) ? $telemetry : [];
$currentPage = max(1, (int) ($telemetry['page'] ?? ($filters['telemetry_page'] ?? 1)));
$totalPages = max(1, (int) ($telemetry['totalPages'] ?? 1));
$totalItems = (int) ($telemetry['total'] ?? 0);
$repoScanCount = (int) ($summary['repoScanCount'] ?? 0);
$cliCount = (int) ($summary['cliCount'] ?? 0);
$successCount = (int) ($summary['successCount'] ?? 0);
$failureCount = (int) ($summary['failureCount'] ?? 0);
$uniqueRepos = (int) ($summary['uniqueRepos'] ?? 0);
$baseFilters = $filters ?? [];
unset($baseFilters['telemetry_page']);
$activeFilters = array_filter($baseFilters, static fn($value) => $value !== null && $value !== '');
$hasFilters = !empty($activeFilters);
$groupBy = in_array((string) ($groupBy ?? 'none'), ['none', 'repo', 'actor'], true) ? (string) $groupBy : 'none';
$groupedItems = is_array($groupedItems ?? null) ? $groupedItems : [];
$prevPage = max(1, $currentPage - 1);
$nextPage = min($totalPages, $currentPage + 1);

if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-chart-line"></i> ReadmeSync telemetry</span>
        <span class="badge"><?= $totalItems ?> logs</span>
    </div>
    <p class="text-muted" style="margin:0">
        Overzicht van ReadmeSync API-gebruik en repo scans. Dit scherm leest server-side uit de API zodat je geen database-console nodig hebt.
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card" style="--icon-color:#3b82f6">
        <div class="stat-card-top">
            <div class="stat-label">Totaal logs</div>
            <div class="stat-icon"><i class="fas fa-database"></i></div>
        </div>
        <div class="stat-value"><?= $totalItems ?></div>
    </div>
    <div class="stat-card" style="--icon-color:#10b981">
        <div class="stat-card-top">
            <div class="stat-label">Repo scans</div>
            <div class="stat-icon"><i class="fas fa-code-branch"></i></div>
        </div>
        <div class="stat-value"><?= $repoScanCount ?></div>
    </div>
    <div class="stat-card" style="--icon-color:#06b6d4">
        <div class="stat-card-top">
            <div class="stat-label">CLI events</div>
            <div class="stat-icon"><i class="fas fa-terminal"></i></div>
        </div>
        <div class="stat-value"><?= $cliCount ?></div>
    </div>
    <div class="stat-card" style="--icon-color:<?= $failureCount > 0 ? '#f59e0b' : '#6b7280' ?>">
        <div class="stat-card-top">
            <div class="stat-label">Succes / fout</div>
            <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-value"><?= $successCount ?> / <?= $failureCount ?></div>
    </div>
    <div class="stat-card" style="--icon-color:#f97316">
        <div class="stat-card-top">
            <div class="stat-label">Unieke repos</div>
            <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
        </div>
        <div class="stat-value"><?= $uniqueRepos ?></div>
    </div>
</div>

<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-filter"></i> Filters</span>
        <?php if ($hasFilters): ?>
            <a href="?page=admin&section=telemetry" class="btn btn-ghost btn-sm">Reset filters</a>
        <?php endif; ?>
    </div>

    <form method="GET" action="?" class="form-grid" style="gap:1rem">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="section" value="telemetry">

        <div class="form-group">
            <label>Event type</label>
            <select name="eventType">
                <option value="">Alle</option>
                <option value="repo_scan" <?= (($filters['eventType'] ?? '') === 'repo_scan') ? 'selected' : '' ?>>Repo scans</option>
                <option value="cli" <?= (($filters['eventType'] ?? '') === 'cli') ? 'selected' : '' ?>>CLI</option>
            </select>
        </div>

        <div class="form-group">
            <label>Repo / owner</label>
            <input type="text" name="repo" value="<?= htmlspecialchars((string) ($filters['repo'] ?? '')) ?>" placeholder="tombomeke / Portfolio">
        </div>

        <div class="form-group">
            <label>Client / user</label>
            <input type="text" name="actor" value="<?= htmlspecialchars((string) ($filters['actor'] ?? '')) ?>" placeholder="portfolio / user id / username">
        </div>

        <div class="form-group">
            <label>Language</label>
            <input type="text" name="language" value="<?= htmlspecialchars((string) ($filters['language'] ?? '')) ?>" placeholder="csharp">
        </div>

        <div class="form-group">
            <label>OS</label>
            <input type="text" name="os" value="<?= htmlspecialchars((string) ($filters['os'] ?? '')) ?>" placeholder="Windows">
        </div>

        <div class="form-group">
            <label>From UTC</label>
            <input type="datetime-local" name="fromUtc" value="<?= htmlspecialchars((string) ($filters['fromUtc'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label>To UTC</label>
            <input type="datetime-local" name="toUtc" value="<?= htmlspecialchars((string) ($filters['toUtc'] ?? '')) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Filteren</button>
        </div>

        <div class="form-group">
            <label>Weergave</label>
            <select name="groupBy">
                <option value="none" <?= $groupBy === 'none' ? 'selected' : '' ?>>Ruwe events</option>
                <option value="repo" <?= $groupBy === 'repo' ? 'selected' : '' ?>>Gegroepeerd per repo + type</option>
                <option value="actor" <?= $groupBy === 'actor' ? 'selected' : '' ?>>Gegroepeerd per client/user + type</option>
            </select>
        </div>
    </form>

    <form method="POST" action="?page=admin&section=telemetry" style="margin-top:1rem" onsubmit="return confirm('Weet je zeker dat je gefilterde telemetry wilt verwijderen? Dit kan niet ongedaan worden gemaakt.');">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="telemetry_action" value="delete_filtered">
        <input type="hidden" name="eventType" value="<?= htmlspecialchars((string) ($filters['eventType'] ?? '')) ?>">
        <input type="hidden" name="repo" value="<?= htmlspecialchars((string) ($filters['repo'] ?? '')) ?>">
        <input type="hidden" name="actor" value="<?= htmlspecialchars((string) ($filters['actor'] ?? '')) ?>">
        <input type="hidden" name="language" value="<?= htmlspecialchars((string) ($filters['language'] ?? '')) ?>">
        <input type="hidden" name="os" value="<?= htmlspecialchars((string) ($filters['os'] ?? '')) ?>">
        <input type="hidden" name="fromUtc" value="<?= htmlspecialchars((string) ($filters['fromUtc'] ?? '')) ?>">
        <input type="hidden" name="toUtc" value="<?= htmlspecialchars((string) ($filters['toUtc'] ?? '')) ?>">
        <input type="hidden" name="groupBy" value="<?= htmlspecialchars($groupBy) ?>">

        <div class="form-grid" style="gap:1rem">
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                    <input type="checkbox" name="onlyFailures" value="1">
                    <span>Verwijder alleen fout-events (extra filter)</span>
                </label>
            </div>
            <div class="form-group">
                <label>Max te verwijderen (veiligheidslimiet)</label>
                <input type="number" min="1" max="20000" step="1" name="take" value="500">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Verwijder gefilterde logs</button>
            </div>
            <p class="text-muted text-sm" style="margin:0">Tip: eerst filters zetten, dan verwijderen. Verwijderen zonder filters is geblokkeerd.</p>
        </div>
    </form>
</div>

<?php if (!empty($apiError)): ?>
    <div class="flash error" style="margin-top:1rem"><?= htmlspecialchars($apiError) ?></div>
<?php endif; ?>

<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-list"></i> <?= $groupBy === 'none' ? 'Recente events' : 'Gegroepeerde events' ?></span>
        <span class="badge"><?= $groupBy === 'none' ? ('Pagina ' . $currentPage . ' / ' . $totalPages) : (count($groupedItems) . ' groepen') ?></span>
    </div>

    <?php if ($groupBy !== 'none' && !empty($groupedItems)): ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= $groupBy === 'actor' ? 'Client / user' : 'Repo' ?></th>
                        <th>Type</th>
                        <th>Aantal</th>
                        <th>Succes / fout</th>
                        <th>Laatste event</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedItems as $group): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($group['groupLabel'] ?? 'onbekend')) ?></td>
                            <td><span class="badge"><?= htmlspecialchars((string) ($group['eventType'] ?? 'unknown')) ?></span></td>
                            <td><?= (int) ($group['count'] ?? 0) ?></td>
                            <td><?= (int) ($group['successCount'] ?? 0) ?> / <?= (int) ($group['failureCount'] ?? 0) ?></td>
                            <td class="text-sm text-muted"><?= !empty($group['lastCreatedAt']) ? date('d/m/Y H:i', strtotime((string) $group['lastCreatedAt'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (empty($items)): ?>
        <p class="empty-state"><i class="fas fa-inbox"></i> Geen telemetry items gevonden.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Type</th>
                        <th>Repo</th>
                        <th>Client / user</th>
                        <th>Language</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $eventType = (string) ($item['eventType'] ?? 'cli');
                        $repoUrl = (string) ($item['repoUrl'] ?? '');
                        $repoOwner = (string) ($item['repoOwner'] ?? '');
                        $repoName = (string) ($item['repoName'] ?? '');
                        $sourceClient = trim((string) ($item['sourceClient'] ?? 'portfolio'));
                        $sourceUserId = trim((string) ($item['sourceUserId'] ?? ''));
                        $sourceUserName = trim((string) ($item['sourceUserName'] ?? ''));
                        $actorLabel = $sourceUserName !== ''
                            ? $sourceUserName . ($sourceUserId !== '' ? (' (#' . $sourceUserId . ')') : '')
                            : ($sourceUserId !== '' ? ('user #' . $sourceUserId) : $sourceClient);
                        $isSuccess = (bool) ($item['success'] ?? false);
                        $statusCode = $item['statusCode'] ?? null;
                        $detail = trim((string) ($item['detail'] ?? ''));
                        ?>
                        <tr>
                            <td class="text-sm text-muted"><?= !empty($item['createdAt']) ? date('d/m/Y H:i', strtotime((string) $item['createdAt'])) : '—' ?></td>
                            <td>
                                <span class="badge <?= $eventType === 'repo_scan' ? 'badge--success' : 'badge' ?>"><?= htmlspecialchars($eventType) ?></span>
                            </td>
                            <td>
                                <?php if ($repoUrl !== ''): ?>
                                    <a href="<?= htmlspecialchars($repoUrl) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($repoOwner !== '' ? $repoOwner . '/' . $repoName : $repoUrl) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm text-muted"><?= htmlspecialchars($actorLabel) ?></td>
                            <td><?= htmlspecialchars((string) ($item['languageScanned'] ?? '—')) ?></td>
                            <td>
                                <span class="badge <?= $isSuccess ? 'badge--success' : 'badge--warning' ?>">
                                    <?= $isSuccess ? 'OK' : 'Error' ?><?= $statusCode ? ' · ' . (int) $statusCode : '' ?>
                                </span>
                            </td>
                            <td class="text-sm text-muted">
                                <?= $detail !== '' ? htmlspecialchars(mb_strimwidth($detail, 0, 90, '…')) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($groupBy === 'none'): ?>
        <div class="form-actions" style="margin-top:1rem;justify-content:space-between;flex-wrap:wrap">
            <a class="btn btn-ghost btn-sm <?= $currentPage <= 1 ? 'disabled' : '' ?>" href="?page=admin&section=telemetry&telemetry_page=<?= $prevPage ?><?= $hasFilters ? '&' . http_build_query($activeFilters) : '' ?>">
                <i class="fas fa-chevron-left"></i> Vorige
            </a>
            <a class="btn btn-ghost btn-sm <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" href="?page=admin&section=telemetry&telemetry_page=<?= $nextPage ?><?= $hasFilters ? '&' . http_build_query($activeFilters) : '' ?>">
                Volgende <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>
