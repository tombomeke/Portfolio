<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php // TODO(profile): Reconcile public settings with admin profile settings so duplicated controls have one clear source of truth. ?>

<!-- Profile information -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-id-card"></i> <?= trans('admin_profile_info') ?></span>
    </div>
    <form method="POST" action="?page=admin&section=profile" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;padding:1.25rem">

            <?php if ($user['profile_photo_path']): ?>
            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_profile_current_photo') ?></label>
                <img src="<?= htmlspecialchars($user['profile_photo_path']) ?>" alt="<?= trans('admin_profile_avatar') ?>"
                     style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border)">
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label><?= trans('admin_profile_username') ?></label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity:.6">
                <span class="form-hint"><?= trans('admin_profile_username_hint') ?></span>
            </div>
            <div class="form-group">
                <label><?= trans('admin_profile_email') ?></label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6">
            </div>

            <div class="form-group">
                <label><?= trans('admin_profile_photo') ?></label>
                <input type="file" name="profile_photo" accept="image/*">
                <span class="form-hint"><?= trans('admin_profile_photo_hint') ?></span>
            </div>
            <div class="form-group">
                <label><?= trans('admin_profile_birthday') ?></label>
                <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
            </div>

            <div class="form-group" style="grid-column:1/-1">
                <label><?= trans('admin_profile_about') ?></label>
                <textarea name="about" rows="4"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label><?= trans('admin_profile_language') ?></label>
                <select name="preferred_language">
                    <option value="nl" <?= ($user['preferred_language'] ?? 'nl') === 'nl' ? 'selected' : '' ?>>🇳🇱 <?= trans('admin_profile_dutch') ?></option>
                    <option value="en" <?= ($user['preferred_language'] ?? 'nl') === 'en' ? 'selected' : '' ?>>🇬🇧 <?= trans('admin_profile_english') ?></option>
                </select>
            </div>
            <div class="form-group">
                <label><?= trans('admin_profile_public') ?></label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-top:.4rem">
                    <input type="checkbox" name="public_profile" value="1" <?= ($user['public_profile'] ?? 1) ? 'checked' : '' ?>>
                    <span><?= trans('admin_profile_public_hint') ?></span>
                </label>
            </div>

            <div class="form-actions" style="grid-column:1/-1">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('admin_profile_save') ?></button>
            </div>
        </div>
    </form>
</div>

<!-- Change password -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-lock"></i> <?= trans('admin_profile_change_password') ?></span>
    </div>
    <form method="POST" action="?page=admin&section=profile">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1rem;padding:1.25rem;max-width:420px">
            <div class="form-group">
                <label><?= trans('admin_profile_current_password') ?> *</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label><?= trans('admin_profile_new_password') ?> *</label>
                <input type="password" name="new_password" required minlength="8">
            </div>
            <div class="form-group">
                <label><?= trans('admin_profile_confirm_password') ?> *</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> <?= trans('admin_profile_change_btn') ?></button>
            </div>
        </div>
    </form>
</div>
