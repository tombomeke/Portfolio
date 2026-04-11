<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="fas fa-user-circle"></i></div>
                <h1 class="auth-title">Welkom terug</h1>
                <p class="auth-subtitle">Log in om door te gaan</p>
            </div>

            <?php if ($error): ?>
                <div class="flash error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="?page=login" class="auth-form-inner">
                <?= \Auth::csrfField() ?>
                <?php if ($redirect): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email"
                           placeholder="jouw@email.com"
                           required autofocus autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Inloggen
                </button>

                <div class="auth-links">
                    <a href="?page=register" class="auth-link">
                        <i class="fas fa-user-plus"></i> Nog geen account? Registreer
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
.auth-header { text-align: center; margin-bottom: 2rem; }
.auth-logo { font-size: 2.5rem; color: var(--primary-color, #3b82f6); margin-bottom: .75rem; }
.auth-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 .4rem; }
.auth-subtitle { color: var(--text-muted, #6b7280); font-size: .9rem; margin: 0; }
.auth-form-inner .form-group { margin-bottom: 1.25rem; }
.auth-form-inner label { display: block; margin-bottom: .4rem; font-size: .85rem; font-weight: 500; }
.auth-form-inner input[type="email"],
.auth-form-inner input[type="password"],
.auth-form-inner input[type="text"] {
    width: 100%; padding: .65rem .9rem; border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 6px; font-size: .95rem; background: transparent;
    transition: border-color .15s;
    box-sizing: border-box;
}
.auth-form-inner input:focus { outline: none; border-color: var(--primary-color, #3b82f6); }
.auth-btn { width: 100%; justify-content: center; padding: .75rem; font-size: 1rem; margin-top: .5rem; }
.auth-links { text-align: center; margin-top: 1.25rem; }
.auth-link { color: var(--primary-color, #3b82f6); text-decoration: none; font-size: .875rem; }
.auth-link:hover { text-decoration: underline; }
.flash { padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1.25rem; font-size: .9rem; }
.flash.error { background: rgba(239,68,68,.1); color: #dc2626; border: 1px solid rgba(239,68,68,.3); }
.flash.success { background: rgba(34,197,94,.1); color: #16a34a; border: 1px solid rgba(34,197,94,.3); }
</style>
