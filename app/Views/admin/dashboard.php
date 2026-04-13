<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Welcome banner -->
<div class="dashboard-welcome">
    <div>
        <small class="dashboard-eyebrow"><?= trans('admin_dashboard_overview') ?></small>
        <h2><?= trans('admin_welcome_back') ?>, <strong><?= htmlspecialchars($authUser['username'] ?? 'admin') ?></strong></h2>
        <p><?= sprintf(trans('admin_dashboard_summary_date'), date('d F Y')) ?></p>
    </div>
    <div class="dashboard-welcome-actions">
        <a href="?page=admin&section=news&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> <?= trans('admin_add_news') ?>
        </a>
        <a href="?page=admin&section=projects&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> <?= trans('admin_add_project') ?>
        </a>
    </div>
</div>

<!-- Stats grid -->
<div class="stats-grid">
    <a href="?page=admin&section=news" class="stat-card stat-card--link" style="--icon-color:#3b82f6">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_stats_news_posts') ?></div>
            <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
        </div>
        <div class="stat-value"><?= $stats['news'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=projects" class="stat-card stat-card--link" style="--icon-color:#8b5cf6">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_stats_projects') ?></div>
            <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
        </div>
        <div class="stat-value"><?= $stats['projects'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=faq" class="stat-card stat-card--link" style="--icon-color:#10b981">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_stats_faq_items') ?><small><?= $stats['faq_categories'] ?? 0 ?> <?= trans('admin_stats_categories') ?></small></div>
            <div class="stat-icon"><i class="fas fa-circle-question"></i></div>
        </div>
        <div class="stat-value"><?= $stats['faq_items'] ?? 0 ?></div>
    </a>
    <?php $hasUnread = ($stats['unread_messages'] ?? 0) > 0; ?>
    <a href="?page=admin&section=contact" class="stat-card stat-card--link <?= $hasUnread ? 'stat-card--alert' : '' ?>" style="--icon-color:<?= $hasUnread ? '#f59e0b' : '#6b7280' ?>">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_stats_messages') ?><small><?= $stats['messages'] ?? 0 ?> <?= trans('admin_stats_total') ?></small></div>
            <div class="stat-icon"><i class="fas fa-envelope<?= $hasUnread ? '' : '-open' ?>"></i></div>
        </div>
        <div class="stat-value">
            <?= $stats['unread_messages'] ?? 0 ?>
            <?php if ($hasUnread): ?><span class="badge badge--warning"><?= trans('admin_stats_unread') ?></span><?php endif; ?>
        </div>
    </a>
    <a href="?page=admin&section=dev-life" class="stat-card stat-card--link" style="--icon-color:#06b6d4">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('skills_title') ?><small><?= $stats['education'] ?? 0 ?> <?= trans('admin_stats_education_short') ?> · <?= $stats['goals'] ?? 0 ?> <?= trans('admin_stats_goals') ?></small></div>
            <div class="stat-icon"><i class="fas fa-laptop-code"></i></div>
        </div>
        <div class="stat-value"><?= $stats['skills'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=tags" class="stat-card stat-card--link" style="--icon-color:#f97316">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_tags') ?></div>
            <div class="stat-icon"><i class="fas fa-tags"></i></div>
        </div>
        <div class="stat-value"><?= $stats['tags'] ?? 0 ?></div>
    </a>
    <?php $hasPending = ($stats['pending_comments'] ?? 0) > 0; ?>
    <a href="?page=admin&section=comments" class="stat-card stat-card--link <?= $hasPending ? 'stat-card--alert' : '' ?>" style="--icon-color:<?= $hasPending ? '#f59e0b' : '#6b7280' ?>">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_comments') ?></div>
            <div class="stat-icon"><i class="fas fa-comments"></i></div>
        </div>
        <div class="stat-value">
            <?= $stats['pending_comments'] ?? 0 ?>
            <?php if ($hasPending): ?><span class="badge badge--warning"><?= trans('admin_stats_pending') ?></span><?php endif; ?>
        </div>
    </a>
    <?php if (($authUser['role'] ?? '') === 'owner'): ?>
    <a href="?page=admin&section=users" class="stat-card stat-card--link" style="--icon-color:#f59e0b">
        <div class="stat-card-top">
            <div class="stat-label"><?= trans('admin_stats_admin_users') ?></div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-value"><?= $stats['admin_users'] ?? 0 ?></div>
    </a>
    <?php endif; ?>
</div>

<!-- Action center -->
<div class="dashboard-quickgrid">
    <div class="card quick-card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-bolt"></i> <?= trans('admin_action_center') ?></span>
        </div>
        <div class="quick-actions">
            <a href="?page=admin&section=contact" class="quick-action <?= ($stats['unread_messages'] ?? 0) > 0 ? 'quick-action--warning' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span><?= trans('admin_view_inbox') ?></span>
                <?php if (($stats['unread_messages'] ?? 0) > 0): ?>
                    <strong><?= (int) ($stats['unread_messages'] ?? 0) ?> <?= trans('admin_stats_unread') ?></strong>
                <?php else: ?>
                    <strong><?= trans('admin_all_read') ?></strong>
                <?php endif; ?>
            </a>
            <a href="?page=admin&section=comments" class="quick-action <?= ($stats['pending_comments'] ?? 0) > 0 ? 'quick-action--warning' : '' ?>">
                <i class="fas fa-comments"></i>
                <span><?= trans('admin_moderate_comments') ?></span>
                <?php if (($stats['pending_comments'] ?? 0) > 0): ?>
                    <strong><?= (int) ($stats['pending_comments'] ?? 0) ?> <?= trans('admin_stats_pending') ?></strong>
                <?php else: ?>
                    <strong><?= trans('admin_no_queue') ?></strong>
                <?php endif; ?>
            </a>
            <a href="?page=admin&section=activity-logs" class="quick-action">
                <i class="fas fa-clock-rotate-left"></i>
                <span><?= trans('admin_recent_activity') ?></span>
                <strong><?= trans('admin_open_logbook') ?></strong>
            </a>
        </div>
    </div>

    <div class="card quick-card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-satellite-dish"></i> <?= trans('admin_system_status') ?></span>
        </div>
        <div class="status-chips">
            <span class="status-chip status-chip--ok"><i class="fas fa-check-circle"></i> <?= trans('admin_status_news_live') ?></span>
            <span class="status-chip status-chip--ok"><i class="fas fa-check-circle"></i> <?= trans('admin_status_faq_live') ?></span>
            <span class="status-chip status-chip--ok"><i class="fas fa-check-circle"></i> <?= trans('admin_status_profiles_live') ?></span>
            <a href="?page=admin&section=wip" class="status-chip status-chip--link"><i class="fas fa-hard-hat"></i> <?= trans('admin_status_wip_manage') ?></a>
            <a href="?page=admin&section=telemetry" class="status-chip status-chip--link"><i class="fas fa-chart-line"></i> <?= trans('admin_telemetry') ?></a>
            <a href="?page=readmesync" class="status-chip status-chip--link"><i class="fas fa-code-branch"></i> <?= trans('admin_status_test_readmesync') ?></a>
        </div>
    </div>
</div>

<!-- Two column: recent news + recent messages -->
<div class="dashboard-columns">

    <!-- Recent news -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-newspaper"></i> <?= trans('admin_recent_news') ?></span>
            <a href="?page=admin&section=news" class="btn btn-ghost btn-sm"><?= trans('roadmap_filter_all') ?></a>
        </div>
        <?php if (empty($recentNews)): ?>
            <p class="empty-state"><i class="fas fa-inbox"></i> <?= trans('admin_no_news_yet') ?></p>
        <?php else: ?>
        <div class="table-wrapper">
        <table class="table">
            <thead><tr><th><?= trans('admin_table_title') ?></th><th><?= trans('admin_table_status') ?></th><th><?= trans('admin_table_date') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentNews as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title_nl'] ?? $item['title_en'] ?? '—') ?></td>
                <td>
                    <?php if ($item['published_at'] && $item['published_at'] <= date('Y-m-d H:i:s')): ?>
                        <span class="badge badge--success"><?= trans('admin_status_published') ?></span>
                    <?php elseif ($item['published_at']): ?>
                        <span class="badge badge--warning"><?= trans('admin_status_scheduled') ?></span>
                    <?php else: ?>
                        <span class="badge"><?= trans('admin_status_draft') ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-muted text-sm"><?= $item['published_at'] ? date('d/m/Y', strtotime($item['published_at'])) : '—' ?></td>
                <td><a href="?page=admin&section=news&action=edit&id=<?= $item['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent messages -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-envelope"></i> <?= trans('admin_recent_messages') ?></span>
            <a href="?page=admin&section=contact" class="btn btn-ghost btn-sm"><?= trans('roadmap_filter_all') ?></a>
        </div>
        <?php if (empty($recentMessages)): ?>
            <p class="empty-state"><i class="fas fa-inbox"></i> <?= trans('admin_no_messages_yet') ?></p>
        <?php else: ?>
        <div class="table-wrapper">
        <table class="table">
            <thead><tr><th><?= trans('admin_table_from') ?></th><th><?= trans('admin_table_subject') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentMessages as $msg): ?>
            <tr class="<?= !$msg['read_at'] ? 'row--unread' : '' ?>">
                <td>
                    <?php if (!$msg['read_at']): ?><i class="fas fa-circle text-warning" style="font-size:.5rem;vertical-align:middle;margin-right:4px"></i><?php endif; ?>
                    <?= htmlspecialchars($msg['name']) ?>
                    <small class="text-muted"><?= htmlspecialchars($msg['email']) ?></small>
                </td>
                <td class="text-sm"><?= htmlspecialchars(mb_strimwidth($msg['subject'] ?? trans('admin_no_subject'), 0, 40, '…')) ?></td>
                <td><a href="?page=admin&section=contact&action=show&id=<?= $msg['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Roadmap status -->
<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <?php $todoCount = (int) ($roadmapMeta['todoCount'] ?? 0); ?>
        <?php $doneCount = (int) ($roadmapMeta['doneCount'] ?? 0); ?>
        <span class="card-title">
            <?php if ($todoCount > 0): ?>
                <i class="fas fa-road" style="color:var(--warning)"></i> <?= trans('admin_roadmap_in_progress') ?>
            <?php else: ?>
                <i class="fas fa-check-circle" style="color:var(--success)"></i> <?= trans('admin_migration_completed') ?>
            <?php endif; ?>
        </span>
        <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;justify-content:flex-end">
            <span class="badge <?= $todoCount > 0 ? 'badge--warning' : 'badge--success' ?>"><?= $doneCount ?> <?= trans('admin_done_label') ?></span>
            <?php if ($todoCount > 0): ?><span class="badge badge--warning"><?= $todoCount ?> <?= trans('admin_todo_label') ?></span><?php endif; ?>
            <?php if (($authUser['role'] ?? '') === 'owner'): ?>
                <a href="?page=admin&section=roadmap" class="btn btn-ghost btn-sm"><?= trans('admin_manage_roadmap') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($roadmapMeta['lastSyncAt'])): ?>
        <p class="text-muted text-sm" style="margin:0 0 .8rem">
            <?= trans('admin_source_label') ?>: <?= htmlspecialchars($roadmapMeta['source'] ?? 'manual') ?>
            <?php if (!empty($roadmapMeta['repoUrl'])): ?>
                · <a href="<?= htmlspecialchars($roadmapMeta['repoUrl']) ?>" target="_blank" rel="noopener" class="text-muted"><?= trans('admin_repo_label') ?></a>
            <?php endif; ?>
            · <?= trans('admin_last_synced_on') ?> <?= date('d/m/Y H:i', strtotime((string) $roadmapMeta['lastSyncAt'])) ?>
        </p>
    <?php endif; ?>

    <div class="roadmap-grid">
        <?php foreach (($roadmapItems ?? []) as $item): ?>
            <?php $isDone = (($item['status'] ?? 'todo') === 'done'); ?>
            <div class="roadmap-item <?= $isDone ? 'roadmap-item--done' : 'roadmap-item--todo' ?>">
                <i class="fas <?= $isDone ? 'fa-check' : 'fa-list-check' ?>"></i>
                <div>
                    <strong><?= htmlspecialchars($item['title'] ?? trans('admin_roadmap_item_fallback')) ?></strong>
                    <?php if (!empty($item['description'])): ?>
                        <span><?= htmlspecialchars($item['description']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
