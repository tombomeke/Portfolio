<section class="settings-page">
    <div class="container container--narrow">
        <!-- TODO(profile): done - Reintroduced per-user settings panel and skill management from legacy app. -->
        <h1 class="settings-title"><i class="fas fa-sliders-h"></i> <?= trans('settings_title') ?></h1>

        <?php if (!empty($flash)): ?>
            <div class="flash <?= htmlspecialchars((string) ($flash['type'] ?? 'info')) ?>">
                <?= htmlspecialchars((string) ($flash['message'] ?? '')) ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <h2><i class="fas fa-cog"></i> <?= trans('settings_profile_title') ?></h2>
            <form method="POST" action="?page=settings">
                <?= \Auth::csrfField() ?>
                <input type="hidden" name="settings_action" value="profile">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="preferred_language"><?= trans('settings_preferred_language') ?></label>
                        <select id="preferred_language" name="preferred_language">
                            <option value="nl" <?= ($user['preferred_language'] ?? 'nl') === 'nl' ? 'selected' : '' ?>>Nederlands (NL)</option>
                            <option value="en" <?= ($user['preferred_language'] ?? 'nl') === 'en' ? 'selected' : '' ?>>English (EN)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?= trans('settings_public_profile') ?></label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="public_profile" value="1" <?= ($user['public_profile'] ?? 1) ? 'checked' : '' ?>>
                            <span><?= trans('settings_public_profile_hint') ?></span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label><?= trans('settings_email_notifications') ?></label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="email_notifications" value="1" <?= (int) ($user['email_notifications'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <span><?= trans('settings_email_notifications_hint') ?></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= trans('settings_save') ?></button>
            </form>
        </div>

        <div class="profile-card">
            <h2><i class="fas fa-tools"></i> <?= trans('settings_skills_title') ?></h2>
            <p class="settings-hint"><?= trans('settings_skills_hint') ?></p>

            <?php if (!empty($skills)): ?>
                <div class="settings-skill-list">
                    <?php foreach ($skills as $skill): ?>
                        <div class="settings-skill-item">
                            <div>
                                <strong><?= htmlspecialchars((string) $skill['name']) ?></strong>
                                <div class="settings-skill-meta">
                                    <?= htmlspecialchars((string) $skill['category']) ?> · L<?= (int) $skill['level'] ?>
                                    <?php if (!empty($skill['years_experience'])): ?> · <?= (int) $skill['years_experience'] ?>y<?php endif; ?>
                                    · <?= (int) $skill['is_public'] ? trans('settings_skill_public') : trans('settings_skill_private') ?>
                                </div>
                                <details class="settings-skill-edit">
                                    <summary><?= trans('settings_skill_edit') ?></summary>
                                    <form method="POST" action="?page=settings" class="settings-skill-edit-form">
                                        <?= \Auth::csrfField() ?>
                                        <input type="hidden" name="settings_action" value="update_skill">
                                        <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">

                                        <div class="form-grid-2">
                                            <div class="form-group">
                                                <label><?= trans('settings_skill_name') ?></label>
                                                <input name="skill_name" type="text" required maxlength="120" value="<?= htmlspecialchars((string) $skill['name']) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label><?= trans('settings_skill_category') ?></label>
                                                <input name="skill_category" type="text" required maxlength="80" value="<?= htmlspecialchars((string) $skill['category']) ?>">
                                            </div>
                                            <div class="form-group">
                                                <label><?= trans('settings_skill_level') ?></label>
                                                <select name="skill_level">
                                                    <?php for ($lvl = 1; $lvl <= 5; $lvl++): ?>
                                                        <option value="<?= $lvl ?>" <?= (int) $skill['level'] === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label><?= trans('settings_skill_years_experience') ?></label>
                                                <input name="skill_years_experience" type="number" min="0" max="60" value="<?= htmlspecialchars((string) ($skill['years_experience'] ?? '')) ?>">
                                            </div>
                                            <div class="form-group" style="grid-column:1/-1">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" name="skill_is_public" value="1" <?= (int) ($skill['is_public'] ?? 1) === 1 ? 'checked' : '' ?>>
                                                    <span><?= trans('settings_skill_public') ?></span>
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> <?= trans('settings_skill_update') ?></button>
                                    </form>
                                </details>
                            </div>
                            <form method="POST" action="?page=settings" data-confirm-submit="<?= htmlspecialchars(trans('settings_skill_delete_confirm'), ENT_QUOTES, 'UTF-8') ?>">
                                <?= \Auth::csrfField() ?>
                                <input type="hidden" name="settings_action" value="delete_skill">
                                <input type="hidden" name="skill_id" value="<?= (int) $skill['id'] ?>">
                                <button type="submit" class="settings-delete-btn">
                                    <i class="fas fa-trash"></i> <?= trans('admin_action_delete') ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="settings-hint"><?= trans('settings_skills_empty') ?></p>
            <?php endif; ?>

            <form method="POST" action="?page=settings" class="settings-skill-form">
                <?= \Auth::csrfField() ?>
                <input type="hidden" name="settings_action" value="add_skill">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="skill_name"><?= trans('settings_skill_name') ?></label>
                        <input id="skill_name" name="skill_name" type="text" required maxlength="120">
                    </div>
                    <div class="form-group">
                        <label for="skill_category"><?= trans('settings_skill_category') ?></label>
                        <input id="skill_category" name="skill_category" type="text" required maxlength="80">
                    </div>
                    <div class="form-group">
                        <label for="skill_level"><?= trans('settings_skill_level') ?></label>
                        <select id="skill_level" name="skill_level">
                            <option value="1">1 - Beginner</option>
                            <option value="2">2</option>
                            <option value="3" selected>3 - Intermediate</option>
                            <option value="4">4</option>
                            <option value="5">5 - Advanced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="skill_years_experience"><?= trans('settings_skill_years_experience') ?></label>
                        <input id="skill_years_experience" name="skill_years_experience" type="number" min="0" max="60">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="skill_is_public" value="1" checked>
                            <span><?= trans('settings_skill_public') ?></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> <?= trans('settings_skill_add') ?></button>
            </form>
        </div>
    </div>
</section>

<div class="confirm-modal" id="settingsConfirmModal" hidden aria-hidden="true">
    <div class="confirm-modal__backdrop" data-modal-close="1"></div>
    <div class="confirm-modal__panel" role="dialog" aria-modal="true" aria-labelledby="settingsConfirmTitle">
        <h3 id="settingsConfirmTitle"><?= trans('settings_skill_delete_confirm') ?></h3>
        <p id="settingsConfirmMessage"><?= trans('settings_skill_delete_confirm') ?></p>
        <div class="confirm-modal__actions">
            <button type="button" class="btn btn-secondary" data-modal-cancel="1"><?= trans('admin_cancel') ?></button>
            <button type="button" class="btn btn-danger" data-modal-confirm="1"><?= trans('admin_confirm') ?></button>
        </div>
    </div>
</div>

<style>
.settings-page { padding: 3.5rem 0 4rem; }
.settings-page .container--narrow { display: grid; gap: 1.25rem; }
.settings-title { margin-bottom: .2rem; font-size: 1.45rem; display:flex; align-items:center; gap:.55rem; letter-spacing: -.02em; }
.settings-hint { color: var(--text-muted,#94a3b8); font-size: .92rem; margin-bottom: 1rem; line-height: 1.6; }
.settings-page .profile-card {
    background: linear-gradient(180deg, rgba(30,41,59,.96), rgba(15,23,42,.96));
    border: 1px solid rgba(71, 85, 105, .55);
    box-shadow: 0 18px 42px rgba(2, 6, 23, .28);
    border-radius: 16px;
    padding: 1.35rem 1.35rem 1.25rem;
}
.settings-page .profile-card h2 {
    margin-bottom: .95rem;
    font-size: 1.05rem;
    letter-spacing: -.01em;
}
.settings-page .form-group label { color: var(--text-primary, #f8fafc); font-weight: 600; }
.settings-page select,
.settings-page input[type="text"],
.settings-page input[type="number"] {
    min-height: 48px;
}
.settings-skill-list { display: grid; gap: .8rem; margin-bottom: 1rem; }
.settings-skill-item {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:1rem;
    padding: .95rem 1rem;
    border:1px solid rgba(71, 85, 105, .55);
    border-radius:12px;
    background: rgba(255,255,255,.03);
}
.settings-skill-item:hover { border-color: rgba(59,130,246,.45); background: rgba(59,130,246,.06); }
.settings-skill-item > div:first-child { min-width: 0; }
.settings-skill-meta { margin-top:.2rem; color: var(--text-muted,#94a3b8); font-size:.83rem; line-height: 1.5; }
.settings-skill-edit { margin-top: .55rem; }
.settings-skill-edit summary { cursor: pointer; color: #93c5fd; font-size: .86rem; font-weight: 600; }
.settings-skill-edit-form {
    margin-top: .75rem;
    padding: .85rem;
    border:1px solid rgba(71, 85, 105, .55);
    border-radius:12px;
    background: rgba(15,23,42,.48);
}
.settings-delete-btn {
    background:none;
    border:1px solid rgba(239,68,68,.35);
    color:#fca5a5;
    border-radius:10px;
    padding:.5rem .7rem;
    cursor:pointer;
    white-space: nowrap;
}
.settings-delete-btn:hover { background: rgba(239,68,68,.15); border-color: rgba(248,113,113,.55); }
.settings-skill-form { margin-top: .9rem; }
.checkbox-inline { display:inline-flex; align-items:flex-start; gap:.6rem; line-height: 1.45; }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.confirm-modal { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 1.5rem; z-index: 1200; }
.confirm-modal.is-open { display: flex; }
.confirm-modal__backdrop { position: absolute; inset: 0; background: rgba(2, 6, 23, .72); backdrop-filter: blur(1px); }
.confirm-modal__panel { position: relative; width: min(460px, 95vw); border-radius: 12px; border: 1px solid rgba(148, 163, 184, .3); background: var(--surface-color, #1e293b); color: var(--text-primary, #f8fafc); box-shadow: 0 18px 40px rgba(2, 6, 23, .38); padding: 1rem 1rem .9rem; }
.confirm-modal__panel h3 { margin: 0 0 .45rem; font-size: 1.03rem; }
.confirm-modal__panel p { margin: 0; color: var(--text-secondary, #cbd5e1); font-size: .92rem; }
.confirm-modal__actions { margin-top: .95rem; display: flex; justify-content: flex-end; gap: .55rem; }
@media (max-width: 720px) {
  .form-grid-2 { grid-template-columns: 1fr; }
  .settings-skill-item { flex-direction: column; align-items: flex-start; }
    .settings-page .profile-card { padding: 1.1rem; }
}
</style>

<script>
(function () {
    const modal = document.getElementById('settingsConfirmModal');
    if (!modal) {
        return;
    }

    const messageEl = document.getElementById('settingsConfirmMessage');
    const confirmBtn = modal.querySelector('[data-modal-confirm="1"]');
    const cancelBtn = modal.querySelector('[data-modal-cancel="1"]');
    const closeBackdrop = modal.querySelector('[data-modal-close="1"]');
    let pendingForm = null;

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        pendingForm = null;
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm-submit')) {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        if (messageEl) {
            messageEl.textContent = form.getAttribute('data-confirm-submit') || '';
        }
        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    });

    confirmBtn && confirmBtn.addEventListener('click', function () {
        if (pendingForm) {
            pendingForm.submit();
        }
    });

    cancelBtn && cancelBtn.addEventListener('click', closeModal);
    closeBackdrop && closeBackdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
})();
</script>
