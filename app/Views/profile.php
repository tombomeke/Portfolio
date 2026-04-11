<section class="public-profile-page">
    <div class="container">

        <?php
        $authUser   = $_SESSION['auth_user'] ?? null;
        $isOwn      = $authUser && (int) $authUser['id'] === (int) $profileUser['id'];
        $photoPath  = !empty($profileUser['profile_photo_path'])
            ? 'public/images/uploads/avatars/' . htmlspecialchars($profileUser['profile_photo_path'])
            : null;
        ?>

        <div class="profile-hero">
            <div class="profile-avatar-wrap">
                <?php if ($photoPath): ?>
                    <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($profileUser['username']) ?>" class="profile-avatar-img">
                <?php else: ?>
                    <div class="profile-avatar-placeholder">
                        <?= htmlspecialchars(strtoupper(substr($profileUser['username'], 0, 1))) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-hero-info">
                <h1><?= htmlspecialchars($profileUser['username']) ?></h1>
                <?php if (!empty($profileUser['birthday'])): ?>
                    <p class="profile-meta"><i class="fas fa-birthday-cake"></i> <?= date('j F Y', strtotime($profileUser['birthday'])) ?></p>
                <?php endif; ?>
                <?php
                $roleLabels = ['owner' => 'Site owner', 'admin' => 'Beheerder', 'user' => 'Lid'];
                $role = $profileUser['role'] ?? 'user';
                ?>
                <span class="profile-role-badge profile-role-badge--<?= htmlspecialchars($role) ?>">
                    <?= htmlspecialchars($roleLabels[$role] ?? $role) ?>
                </span>
                <?php if ($isOwn): ?>
                <div class="profile-own-actions">
                    <a href="?page=admin&section=profile" class="btn btn-outline btn-sm">
                        <i class="fas fa-user-edit"></i> Profiel bewerken
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($profileUser['about'])): ?>
        <div class="profile-card">
            <h2><i class="fas fa-user"></i> Over mij</h2>
            <p><?= nl2br(htmlspecialchars($profileUser['about'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="profile-card">
            <h2><i class="fas fa-info-circle"></i> Gegevens</h2>
            <dl class="profile-dl">
                <dt>Gebruikersnaam</dt>
                <dd><?= htmlspecialchars($profileUser['username']) ?></dd>
                <dt>Lid sinds</dt>
                <dd><?= date('j F Y', strtotime($profileUser['created_at'])) ?></dd>
                <?php if (!empty($profileUser['preferred_language'])): ?>
                <dt>Voorkeurstaal</dt>
                <dd><?= $profileUser['preferred_language'] === 'en' ? '🇬🇧 Engels' : '🇳🇱 Nederlands' ?></dd>
                <?php endif; ?>
            </dl>
        </div>

    </div>
</section>

<style>
.public-profile-page { padding: 4rem 0; }
.profile-hero { display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
.profile-avatar-wrap { flex-shrink: 0; }
.profile-avatar-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border-color, #e5e7eb); }
.profile-avatar-placeholder { width: 100px; height: 100px; border-radius: 50%; background: var(--primary-color, #3b82f6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; }
.profile-hero-info h1 { margin: 0 0 .35rem; font-size: 1.75rem; }
.profile-meta { color: var(--text-muted, #6b7280); font-size: .9rem; margin: 0 0 .5rem; }
.profile-role-badge { display: inline-block; padding: .2rem .65rem; border-radius: 99px; font-size: .75rem; font-weight: 600; }
.profile-role-badge--owner { background: rgba(234,179,8,.15); color: #854d0e; }
.profile-role-badge--admin { background: rgba(59,130,246,.12); color: var(--primary-color, #3b82f6); }
.profile-role-badge--user  { background: rgba(107,114,128,.1); color: #374151; }
.profile-own-actions { margin-top: .75rem; }
.profile-card { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 10px; padding: 1.5rem; margin-bottom: 1.5rem; }
.profile-card h2 { font-size: 1.05rem; margin: 0 0 1rem; display: flex; align-items: center; gap: .5rem; }
.profile-card p { line-height: 1.7; margin: 0; }
.profile-dl { display: grid; grid-template-columns: 140px 1fr; gap: .5rem 1rem; margin: 0; }
.profile-dl dt { font-weight: 600; font-size: .85rem; color: var(--text-muted, #6b7280); align-self: start; padding-top: .15rem; }
.profile-dl dd { margin: 0; font-size: .9rem; }
</style>
