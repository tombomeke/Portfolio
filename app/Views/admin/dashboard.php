<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">News items</div>
        <div class="value"><?= $stats['news'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Projects</div>
        <div class="value"><?= $stats['projects'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="label">FAQ categories</div>
        <div class="value"><?= $stats['faq_categories'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="label">FAQ items</div>
        <div class="value"><?= $stats['faq_items'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Contact messages</div>
        <div class="value"><?= $stats['messages'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Unread messages</div>
        <div class="value <?= ($stats['unread_messages'] ?? 0) > 0 ? 'danger' : 'success' ?>">
            <?= $stats['unread_messages'] ?? 0 ?>
        </div>
    </div>
    <?php if ($authUser['role'] === 'owner'): ?>
    <div class="stat-card">
        <div class="label">Admin users</div>
        <div class="value"><?= $stats['users'] ?? 0 ?></div>
    </div>
    <?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem">
    <a href="?page=admin&section=news&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nieuwsbericht toevoegen
    </a>
    <a href="?page=admin&section=projects&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Project toevoegen
    </a>
    <a href="?page=admin&section=faq&action=item-create" class="btn btn-ghost">
        <i class="fas fa-plus"></i> FAQ-item toevoegen
    </a>
    <a href="?page=admin&section=contact" class="btn btn-ghost">
        <i class="fas fa-envelope"></i> Contact berichten
        <?php if (($stats['unread_messages'] ?? 0) > 0): ?>
            (<?= $stats['unread_messages'] ?> ongelezen)
        <?php endif; ?>
    </a>
</div>
