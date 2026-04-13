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
        <span class="card-title"><i class="fas fa-chart-line"></i> <?= trans('admin_telemetry_title') ?></span>
        <span class="badge"><?= $totalItems ?> <?= trans('admin_telemetry_logs') ?></span>
    </div>
    <p class="text-muted" style="margin:0">
        <?= trans('admin_telemetry_intro') ?>
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
        <span class="card-title"><i class="fas fa-filter"></i> <?= trans('admin_telemetry_filters') ?></span>
        <?php if ($hasFilters): ?>
            <a href="?page=admin&section=telemetry" class="btn btn-ghost btn-sm"><?= trans('admin_telemetry_reset_filters') ?></a>
        <?php endif; ?>
    </div>

    <form method="GET" action="?" class="form-grid" style="gap:1rem">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="section" value="telemetry">

        <div class="form-group">
            <label><?= trans('admin_telemetry_event_type') ?></label>
            <select name="eventType">
                <option value=""><?= trans('admin_telemetry_all') ?></option>
                <option value="repo_scan" <?= (($filters['eventType'] ?? '') === 'repo_scan') ? 'selected' : '' ?>><?= trans('admin_telemetry_repo_scans') ?></option>
                <option value="cli" <?= (($filters['eventType'] ?? '') === 'cli') ? 'selected' : '' ?>>CLI</option>
            </select>
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_repo_owner') ?></label>
            <input type="text" name="repo" value="<?= htmlspecialchars((string) ($filters['repo'] ?? '')) ?>" placeholder="tombomeke / Portfolio">
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_client_user') ?></label>
            <input type="text" name="actor" value="<?= htmlspecialchars((string) ($filters['actor'] ?? '')) ?>" placeholder="portfolio / user id / username">
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_language') ?></label>
            <input type="text" name="language" value="<?= htmlspecialchars((string) ($filters['language'] ?? '')) ?>" placeholder="csharp">
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_os') ?></label>
            <input type="text" name="os" value="<?= htmlspecialchars((string) ($filters['os'] ?? '')) ?>" placeholder="Windows">
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_from_utc') ?></label>
            <input type="datetime-local" name="fromUtc" value="<?= htmlspecialchars((string) ($filters['fromUtc'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_to_utc') ?></label>
            <input type="datetime-local" name="toUtc" value="<?= htmlspecialchars((string) ($filters['toUtc'] ?? '')) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> <?= trans('admin_telemetry_filter_btn') ?></button>
        </div>

        <div class="form-group">
            <label><?= trans('admin_telemetry_display') ?></label>
            <select name="groupBy">
                <option value="none" <?= $groupBy === 'none' ? 'selected' : '' ?>><?= trans('admin_telemetry_raw_events') ?></option>
                <option value="repo" <?= $groupBy === 'repo' ? 'selected' : '' ?>><?= trans('admin_telemetry_grouped_repo') ?></option>
                <option value="actor" <?= $groupBy === 'actor' ? 'selected' : '' ?>><?= trans('admin_telemetry_grouped_actor') ?></option>
            </select>
        </div>
    </form>

    <form method="POST" action="?page=admin&section=telemetry" style="margin-top:1rem" onsubmit="return confirm('<?= addslashes(trans('admin_telemetry_delete_confirm')) ?>')">
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
                    <span><?= trans('admin_telemetry_only_failures') ?></span>
                </label>
            </div>
            <div class="form-group">
                <label><?= trans('admin_telemetry_max_delete') ?></label>
                <input type="number" min="1" max="20000" step="1" name="take" value="500">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> <?= trans('admin_telemetry_delete_btn') ?></button>
            </div>
            <p class="text-muted text-sm" style="margin:0"><?= trans('admin_telemetry_delete_tip') ?></p>
        </div>
    </form>
</div>

<?php if (!empty($apiError)): ?>
    <div class="flash error" style="margin-top:1rem"><?= htmlspecialchars((string) $apiError) ?></div>
<?php endif; ?>

<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-list"></i> <?= $groupBy === 'none' ? trans('admin_telemetry_recent_events') : trans('admin_telemetry_grouped_events') ?></span>
        <span class="badge"><?= $groupBy === 'none' ? (trans('admin_telemetry_page') . ' ' . $currentPage . ' / ' . $totalPages) : (count($groupedItems) . ' ' . trans('admin_telemetry_groups')) ?></span>
    <?php if ($groupBy !== 'none' && !empty($groupedItems)): ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= $groupBy === 'actor' ? trans('admin_telemetry_client_user') : trans('admin_telemetry_repo') ?></th>
                        <th><?= trans('admin_telemetry_type') ?></th>
                        <th><?= trans('admin_telemetry_count') ?></th>
                        <th><?= trans('admin_telemetry_success_fail') ?></th>
                        <th><?= trans('admin_telemetry_last_event') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groupedItems as $group): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($group['groupLabel'] ?? trans('admin_telemetry_unknown'))) ?></td>
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
        <p class="empty-state"><i class="fas fa-inbox"></i> <?= trans('admin_telemetry_no_items') ?></p>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= trans('admin_telemetry_date') ?></th>
                        <th><?= trans('admin_telemetry_type') ?></th>
                        <th><?= trans('admin_telemetry_repo') ?></th>
                        <th><?= trans('admin_telemetry_client_user') ?></th>
                        <th><?= trans('admin_telemetry_language') ?></th>
                        <th><?= trans('admin_telemetry_status') ?></th>
                        <th><?= trans('admin_telemetry_details') ?></th>
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
                        $userLabel = trans('admin_telemetry_user_label');
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
                                    <?= $isSuccess ? trans('admin_telemetry_ok') : trans('admin_telemetry_error') ?><?= $statusCode ? ' · ' . (int) $statusCode : '' ?>
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
                <i class="fas fa-chevron-left"></i> <?= trans('admin_telemetry_previous') ?>
            </a>
            <a class="btn btn-ghost btn-sm <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" href="?page=admin&section=telemetry&telemetry_page=<?= $nextPage ?><?= $hasFilters ? '&' . http_build_query($activeFilters) : '' ?>">
                <?= trans('admin_telemetry_next') ?> <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>
