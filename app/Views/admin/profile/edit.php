<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Profile information -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-id-card"></i> Profielinformatie</span>
    </div>
    <form method="POST" action="?page=admin&section=profile" enctype="multipart/form-data">
        <?= \Auth::csrfField() ?>
        <div class="form-grid form-grid-2" style="gap:1.25rem;padding:1.25rem">

            <?php if ($user['profile_photo_path']): ?>
            <div class="form-group" style="grid-column:1/-1">
                <label>Huidige foto</label>
                <img src="<?= htmlspecialchars($user['profile_photo_path']) ?>" alt="Avatar"
                     style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--border)">
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Gebruikersnaam</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="opacity:.6">
                <span class="form-hint">Gebruikersnaam kan niet gewijzigd worden.</span>
            </div>
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6">
            </div>

            <div class="form-group">
                <label>Profielfoto</label>
                <input type="file" name="profile_photo" accept="image/*">
                <span class="form-hint">Max 2MB. JPG, PNG, WEBP. Vierkant werkt het best.</span>
            </div>
            <div class="form-group">
                <label>Verjaardag</label>
                <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
            </div>

            <div class="form-group" style="grid-column:1/-1">
                <label>Over mij</label>
                <textarea name="about" rows="4"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Voorkeurstaal</label>
                <select name="preferred_language">
                    <option value="nl" <?= ($user['preferred_language'] ?? 'nl') === 'nl' ? 'selected' : '' ?>>🇳🇱 Nederlands</option>
                    <option value="en" <?= ($user['preferred_language'] ?? 'nl') === 'en' ? 'selected' : '' ?>>🇬🇧 English</option>
                </select>
            </div>
            <div class="form-group">
                <label>Openbaar profiel</label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;margin-top:.4rem">
                    <input type="checkbox" name="public_profile" value="1" <?= ($user['public_profile'] ?? 1) ? 'checked' : '' ?>>
                    <span>Profiel zichtbaar voor bezoekers</span>
                </label>
            </div>

            <div class="form-actions" style="grid-column:1/-1">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Opslaan</button>
            </div>
        </div>
    </form>
</div>

<!-- Change password -->
<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-lock"></i> Wachtwoord wijzigen</span>
    </div>
    <form method="POST" action="?page=admin&section=profile">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1rem;padding:1.25rem;max-width:420px">
            <div class="form-group">
                <label>Huidig wachtwoord *</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>Nieuw wachtwoord *</label>
                <input type="password" name="new_password" required minlength="8">
            </div>
            <div class="form-group">
                <label>Bevestig nieuw wachtwoord *</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Wijzigen</button>
            </div>
        </div>
    </form>
</div>
