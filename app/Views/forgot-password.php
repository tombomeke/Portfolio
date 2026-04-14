<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="fas fa-key"></i></div>
                <h1 class="auth-title"><?= trans('auth_forgot_password_title') ?></h1>
                <p class="auth-subtitle"><?= trans('auth_forgot_password_subtitle') ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="flash error"><?= htmlspecialchars((string) $error) ?></div>
            <?php endif; ?>

            <?php if (!empty($flash)): ?>
                <div class="flash success"><?= htmlspecialchars((string) $flash) ?></div>
            <?php endif; ?>

            <form method="POST" action="?page=forgot-password" class="auth-form-inner">
                <?= \Auth::csrfField() ?>

                <div class="form-group">
                    <label for="email"><?= trans('contact_email') ?></label>
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder="<?= trans('contact_email_placeholder') ?>">
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="fas fa-paper-plane"></i> <?= trans('auth_send_reset_link') ?>
                </button>

                <div class="auth-links">
                    <a href="?page=login" class="auth-link">
                        <i class="fas fa-arrow-left"></i> <?= trans('nav_login') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>