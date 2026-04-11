<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Welcome banner -->
<div class="dashboard-welcome">
    <div>
        <h2>Welkom terug, <strong><?= htmlspecialchars($authUser['username'] ?? 'admin') ?></strong></h2>
        <p>Hier is een overzicht van je portfolio op <?= date('d F Y') ?>.</p>
    </div>
    <div class="dashboard-welcome-actions">
        <a href="?page=admin&section=news&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nieuws
        </a>
        <a href="?page=admin&section=projects&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Project
        </a>
    </div>
</div>

<!-- Stats grid -->
<div class="stats-grid">
    <a href="?page=admin&section=news" class="stat-card stat-card--link" style="--icon-color:#3b82f6">
        <div class="stat-card-top">
            <div class="stat-label">Nieuwsberichten</div>
            <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
        </div>
        <div class="stat-value"><?= $stats['news'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=projects" class="stat-card stat-card--link" style="--icon-color:#8b5cf6">
        <div class="stat-card-top">
            <div class="stat-label">Projecten</div>
            <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
        </div>
        <div class="stat-value"><?= $stats['projects'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=faq" class="stat-card stat-card--link" style="--icon-color:#10b981">
        <div class="stat-card-top">
            <div class="stat-label">FAQ-items<small><?= $stats['faq_categories'] ?? 0 ?> categorieën</small></div>
            <div class="stat-icon"><i class="fas fa-circle-question"></i></div>
        </div>
        <div class="stat-value"><?= $stats['faq_items'] ?? 0 ?></div>
    </a>
    <?php $hasUnread = ($stats['unread_messages'] ?? 0) > 0; ?>
    <a href="?page=admin&section=contact" class="stat-card stat-card--link <?= $hasUnread ? 'stat-card--alert' : '' ?>" style="--icon-color:<?= $hasUnread ? '#f59e0b' : '#6b7280' ?>">
        <div class="stat-card-top">
            <div class="stat-label">Berichten<small><?= $stats['messages'] ?? 0 ?> totaal</small></div>
            <div class="stat-icon"><i class="fas fa-envelope<?= $hasUnread ? '' : '-open' ?>"></i></div>
        </div>
        <div class="stat-value">
            <?= $stats['unread_messages'] ?? 0 ?>
            <?php if ($hasUnread): ?><span class="badge badge--warning">ongelezen</span><?php endif; ?>
        </div>
    </a>
    <a href="?page=admin&section=dev-life" class="stat-card stat-card--link" style="--icon-color:#06b6d4">
        <div class="stat-card-top">
            <div class="stat-label">Skills<small><?= $stats['education'] ?? 0 ?> opl. · <?= $stats['goals'] ?? 0 ?> doelen</small></div>
            <div class="stat-icon"><i class="fas fa-laptop-code"></i></div>
        </div>
        <div class="stat-value"><?= $stats['skills'] ?? 0 ?></div>
    </a>
    <a href="?page=admin&section=tags" class="stat-card stat-card--link" style="--icon-color:#f97316">
        <div class="stat-card-top">
            <div class="stat-label">Tags</div>
            <div class="stat-icon"><i class="fas fa-tags"></i></div>
        </div>
        <div class="stat-value"><?= $stats['tags'] ?? 0 ?></div>
    </a>
    <?php $hasPending = ($stats['pending_comments'] ?? 0) > 0; ?>
    <a href="?page=admin&section=comments" class="stat-card stat-card--link <?= $hasPending ? 'stat-card--alert' : '' ?>" style="--icon-color:<?= $hasPending ? '#f59e0b' : '#6b7280' ?>">
        <div class="stat-card-top">
            <div class="stat-label">Reacties</div>
            <div class="stat-icon"><i class="fas fa-comments"></i></div>
        </div>
        <div class="stat-value">
            <?= $stats['pending_comments'] ?? 0 ?>
            <?php if ($hasPending): ?><span class="badge badge--warning">wachtend</span><?php endif; ?>
        </div>
    </a>
    <?php if (($authUser['role'] ?? '') === 'owner'): ?>
    <a href="?page=admin&section=users" class="stat-card stat-card--link" style="--icon-color:#f59e0b">
        <div class="stat-card-top">
            <div class="stat-label">Admin-gebruikers</div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </div>
        <div class="stat-value"><?= $stats['users'] ?? 0 ?></div>
    </a>
    <?php endif; ?>
</div>

<!-- Two column: recent news + recent messages -->
<div class="dashboard-columns">

    <!-- Recent news -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-newspaper"></i> Recente nieuwsberichten</span>
            <a href="?page=admin&section=news" class="btn btn-ghost btn-sm">Alle</a>
        </div>
        <?php if (empty($recentNews)): ?>
            <p class="empty-state"><i class="fas fa-inbox"></i> Nog geen nieuwsberichten.</p>
        <?php else: ?>
        <div class="table-wrapper">
        <table class="table">
            <thead><tr><th>Titel</th><th>Status</th><th>Datum</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentNews as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title_nl'] ?? $item['title_en'] ?? '—') ?></td>
                <td>
                    <?php if ($item['published_at'] && $item['published_at'] <= date('Y-m-d H:i:s')): ?>
                        <span class="badge badge--success">Gepubliceerd</span>
                    <?php elseif ($item['published_at']): ?>
                        <span class="badge badge--warning">Gepland</span>
                    <?php else: ?>
                        <span class="badge">Concept</span>
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
            <span class="card-title"><i class="fas fa-envelope"></i> Recente berichten</span>
            <a href="?page=admin&section=contact" class="btn btn-ghost btn-sm">Alle</a>
        </div>
        <?php if (empty($recentMessages)): ?>
            <p class="empty-state"><i class="fas fa-inbox"></i> Nog geen berichten.</p>
        <?php else: ?>
        <div class="table-wrapper">
        <table class="table">
            <thead><tr><th>Van</th><th>Onderwerp</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recentMessages as $msg): ?>
            <tr class="<?= !$msg['read_at'] ? 'row--unread' : '' ?>">
                <td>
                    <?php if (!$msg['read_at']): ?><i class="fas fa-circle text-warning" style="font-size:.5rem;vertical-align:middle;margin-right:4px"></i><?php endif; ?>
                    <?= htmlspecialchars($msg['name']) ?>
                    <small class="text-muted"><?= htmlspecialchars($msg['email']) ?></small>
                </td>
                <td class="text-sm"><?= htmlspecialchars(mb_strimwidth($msg['subject'] ?? 'Geen onderwerp', 0, 40, '…')) ?></td>
                <td><a href="?page=admin&section=contact&action=show&id=<?= $msg['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Migratiestatus -->
<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-check-circle" style="color:var(--success)"></i> Migratie voltooid</span>
        <span class="badge badge--success">Laravel → PHP MVC</span>
    </div>
    <div class="roadmap-grid">
        <div class="roadmap-item roadmap-item--done">
            <i class="fas fa-check"></i>
            <div>
                <strong>Tags</strong>
                <span>Many-to-many tags op nieuwsberichten, tag-filter op news-pagina</span>
            </div>
        </div>
        <div class="roadmap-item roadmap-item--done">
            <i class="fas fa-check"></i>
            <div>
                <strong>News comments</strong>
                <span>Reacties op nieuwsberichten met moderatie en goedkeuringsflow</span>
            </div>
        </div>
        <div class="roadmap-item roadmap-item--done">
            <i class="fas fa-check"></i>
            <div>
                <strong>Activity logs</strong>
                <span>Alle admin-acties worden bijgehouden met filter en paginering</span>
            </div>
        </div>
        <div class="roadmap-item roadmap-item--done">
            <i class="fas fa-check"></i>
            <div>
                <strong>Site settings</strong>
                <span>Dynamische configuratie per groep instelbaar via admin</span>
            </div>
        </div>
        <div class="roadmap-item roadmap-item--done">
            <i class="fas fa-check"></i>
            <div>
                <strong>User profiles + auth</strong>
                <span>Publiek registreren, inloggen, profielpagina en comment-auth</span>
            </div>
        </div>
    </div>
</div>
