<section class="public-profile-page">
    <div class="container">

        <?php
        $authUser   = $_SESSION['auth_user'] ?? null;
        $isOwn      = $authUser && (int) $authUser['id'] === (int) $profileUser['id'];
        $rawPhotoPath = trim((string) ($profileUser['profile_photo_path'] ?? ''));
        $photoPath = null;
        if ($rawPhotoPath !== '') {
            $photoPath = preg_match('#^public/images/uploads/#', $rawPhotoPath)
                ? $rawPhotoPath
                : 'public/images/uploads/avatars/' . ltrim($rawPhotoPath, '/');
        }
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
                $roleLabels = [
                    'owner' => trans('profile_role_owner'),
                    'admin' => trans('profile_role_admin'),
                    'user'  => trans('profile_role_member'),
                ];
                $role = $profileUser['role'] ?? 'user';
                ?>
                <span class="profile-role-badge profile-role-badge--<?= htmlspecialchars($role) ?>">
                    <?= htmlspecialchars($roleLabels[$role] ?? $role) ?>
                </span>
                <?php if ($isOwn): ?>
                <div class="profile-own-actions">
                    <a href="?page=admin&section=profile" class="btn btn-outline btn-sm">
                        <i class="fas fa-user-edit"></i> <?= trans('profile_edit') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($profileUser['about'])): ?>
        <div class="profile-card">
            <h2><i class="fas fa-user"></i> <?= trans('profile_about_me') ?></h2>
            <p><?= nl2br(htmlspecialchars($profileUser['about'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="profile-card">
            <h2><i class="fas fa-info-circle"></i> <?= trans('profile_details') ?></h2>
            <dl class="profile-dl">
                <dt><?= trans('profile_username') ?></dt>
                <dd><?= htmlspecialchars($profileUser['username']) ?></dd>
                <dt><?= trans('profile_member_since') ?></dt>
                <dd><?= date('j F Y', strtotime($profileUser['created_at'])) ?></dd>
                <?php if (!empty($profileUser['preferred_language'])): ?>
                <dt><?= trans('profile_preferred_language') ?></dt>
                <dd><?= $profileUser['preferred_language'] === 'en' ? trans('profile_lang_en') : trans('profile_lang_nl') ?></dd>
                <?php endif; ?>
            </dl>
        </div>

    </div>
</section>

<style>
.public-profile-page { padding: 4rem 0; }
.profile-hero { display: flex; align-items: center; gap: 2rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
.profile-avatar-wrap { flex-shrink: 0; }
.profile-avatar-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border-color, #334155); }
.profile-avatar-placeholder { width: 100px; height: 100px; border-radius: 50%; background: var(--primary-color, #3b82f6); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; }
.profile-hero-info h1 { margin: 0 0 .35rem; font-size: 1.75rem; }
.profile-meta { color: var(--text-muted, #6b7280); font-size: .9rem; margin: 0 0 .5rem; }
.profile-role-badge { display: inline-block; padding: .2rem .65rem; border-radius: 99px; font-size: .75rem; font-weight: 600; }
.profile-role-badge--owner { background: rgba(234,179,8,.15); color: #854d0e; }
.profile-role-badge--admin { background: rgba(59,130,246,.12); color: var(--primary-color, #3b82f6); }
.profile-role-badge--user  { background: rgba(107,114,128,.1); color: var(--text-secondary, #cbd5e1); }
.profile-own-actions { margin-top: .75rem; }
.profile-card { background: var(--surface-color, #1e293b); border: 1px solid var(--border-color, #334155); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.profile-card h2 { font-size: 1.05rem; margin: 0 0 1rem; display: flex; align-items: center; gap: .5rem; }
.profile-card p { line-height: 1.7; margin: 0; color: var(--text-secondary, #cbd5e1); }
.profile-dl { display: grid; grid-template-columns: 140px 1fr; gap: .5rem 1rem; margin: 0; }
.profile-dl dt { font-weight: 600; font-size: .85rem; color: var(--text-muted, #6b7280); align-self: start; padding-top: .15rem; }
.profile-dl dd { margin: 0; font-size: .95rem; color: var(--text-primary, #f8fafc); }
@media (max-width: 680px) {
    .profile-hero { gap: 1rem; }
    .profile-dl { grid-template-columns: 1fr; gap: .2rem; }
    .profile-dl dt { padding-top: .6rem; }
}
</style>
