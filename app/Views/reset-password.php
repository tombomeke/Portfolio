<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="fas fa-unlock-alt"></i></div>
                <h1 class="auth-title"><?= trans('auth_reset_password_title') ?></h1>
                <p class="auth-subtitle"><?= trans('auth_reset_password_subtitle') ?></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="flash error"><?= htmlspecialchars((string) $error) ?></div>
            <?php endif; ?>

            <?php if (!empty($flash)): ?>
                <div class="flash success"><?= htmlspecialchars((string) $flash) ?></div>
            <?php endif; ?>

            <?php if (empty($tokenValid)): ?>
                <div class="flash error"><?= trans('auth_reset_invalid') ?></div>
            <?php else: ?>
                <form method="POST" action="?page=reset-password" class="auth-form-inner">
                    <?= \Auth::csrfField() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars((string) $token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="password"><?= trans('auth_new_password') ?></label>
                        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation"><?= trans('auth_new_password_confirm') ?></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary auth-btn">
                        <i class="fas fa-lock-open"></i> <?= trans('auth_reset_password') ?>
                    </button>

                    <div class="auth-links">
                        <a href="?page=login" class="auth-link">
                            <i class="fas fa-arrow-left"></i> <?= trans('nav_login') ?>
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>