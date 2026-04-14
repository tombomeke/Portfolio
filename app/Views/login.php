<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="fas fa-user-circle"></i></div>
                <h1 class="auth-title"><?= trans('auth_login_welcome') ?></h1>
                <p class="auth-subtitle"><?= trans('auth_login_subtitle') ?></p>
            </div>

            <?php if ($error): ?>
                <div class="flash error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($flash)): ?>
                <div class="flash success"><?= htmlspecialchars((string) $flash) ?></div>
            <?php endif; ?>

            <form method="POST" action="?page=login" class="auth-form-inner">
                <?= \Auth::csrfField() ?>
                <?php if ($redirect): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="email"><?= trans('contact_email') ?></label>
                    <input type="email" id="email" name="email"
                           placeholder="<?= trans('contact_email_placeholder') ?>"
                           data-email-invalid="<?= htmlspecialchars(trans('form_email_invalid'), ENT_QUOTES, 'UTF-8') ?>"
                           required autofocus autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password"><?= trans('auth_password') ?></label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="fas fa-sign-in-alt"></i> <?= trans('nav_login') ?>
                </button>

                <div class="auth-links">
                    <a href="?page=forgot-password" class="auth-link">
                        <i class="fas fa-key"></i> <?= trans('auth_forgot_password') ?>
                    </a>
                    <a href="?page=register" class="auth-link">
                        <i class="fas fa-user-plus"></i> <?= trans('auth_no_account_register') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
.auth-page { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; }
.auth-container { width: 100%; max-width: 420px; }
.auth-card { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 12px; padding: 2.5rem; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    <?php // TODO(ui): Refine auth heading and input contrast on login/register pages. ?>
.auth-header { text-align: center; margin-bottom: 2rem; }
.auth-logo { font-size: 2.5rem; color: var(--primary-color, #3b82f6); margin-bottom: .75rem; }
.auth-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 .4rem; color: #0f172a; }
.auth-subtitle { color: #475569; font-size: .9rem; margin: 0; }
.auth-form-inner .form-group { margin-bottom: 1.25rem; }
.auth-form-inner label { display: block; margin-bottom: .4rem; font-size: .85rem; font-weight: 600; color: #334155; }
.auth-form-inner input[type="email"],
.auth-form-inner input[type="password"],
.auth-form-inner input[type="text"] {
    width: 100%; padding: .75rem .95rem; border: 1px solid #cbd5e1;
    border-radius: 8px; font-size: .95rem; background: #fff; color: #0f172a;
    transition: border-color .15s;
    box-sizing: border-box;
}
.auth-form-inner input::placeholder { color: #94a3b8; }
.auth-form-inner input:focus { outline: none; border-color: var(--primary-color, #3b82f6); box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.auth-btn { width: 100%; justify-content: center; padding: .75rem; font-size: 1rem; margin-top: .5rem; }
.auth-links { text-align: center; margin-top: 1.25rem; }
.auth-link { color: var(--primary-color, #3b82f6); text-decoration: none; font-size: .875rem; }
.auth-link:hover { text-decoration: underline; }
</style>

<script>
(function () {
    document.querySelectorAll('input[type="email"][data-email-invalid]').forEach(function (input) {
        var message = input.dataset.emailInvalid || '';
        input.addEventListener('invalid', function () {
            this.setCustomValidity(message);
        });
        input.addEventListener('input', function () {
            this.setCustomValidity('');
        });
    });
})();
</script>
