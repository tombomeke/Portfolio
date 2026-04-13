<section class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo"><i class="fas fa-user-plus"></i></div>
                <h1 class="auth-title"><?= trans('auth_register_title') ?></h1>
                <p class="auth-subtitle"><?= trans('auth_register_subtitle') ?></p>
            </div>

            <?php if ($error): ?>
                <div class="flash error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="?page=register" class="auth-form-inner">
                <?= \Auth::csrfField() ?>

                <div class="form-group">
                      <label for="name"><?= trans('auth_full_name') ?></label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                          placeholder="<?= trans('auth_full_name_placeholder') ?>"
                           required autofocus autocomplete="name">
                </div>

                <div class="form-group">
                    <label for="email"><?= trans('contact_email') ?></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                           placeholder="<?= trans('contact_email_placeholder') ?>"
                           data-email-invalid="<?= htmlspecialchars(trans('form_email_invalid'), ENT_QUOTES, 'UTF-8') ?>"
                           required autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password"><?= trans('auth_password') ?></label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required autocomplete="new-password">
                    <div id="password-requirements" class="pw-requirements">
                        <div class="pw-req" id="req-length"><span class="pw-icon"></span> <?= trans('auth_pw_req_length') ?></div>
                        <div class="pw-req" id="req-upper"><span class="pw-icon"></span> <?= trans('auth_pw_req_upper') ?></div>
                        <div class="pw-req" id="req-number"><span class="pw-icon"></span> <?= trans('auth_pw_req_number') ?></div>
                        <div class="pw-req" id="req-special"><span class="pw-icon"></span> <?= trans('auth_pw_req_special') ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><?= trans('auth_confirm_password') ?></label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="••••••••"
                           required autocomplete="new-password">
                    <div id="pw-match"></div>
                </div>

                <button type="submit" class="btn btn-primary auth-btn">
                    <i class="fas fa-user-plus"></i> <?= trans('auth_register_title') ?>
                </button>

                <div class="auth-links">
                    <a href="?page=login" class="auth-link">
                        <i class="fas fa-sign-in-alt"></i> <?= trans('auth_has_account_login') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
.auth-page { min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 3rem 1rem; }
.auth-container { width: 100%; max-width: 440px; }
.auth-card { background: var(--card-bg, #fff); border: 1px solid var(--border-color, #e5e7eb); border-radius: 12px; padding: 2.5rem; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
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
.auth-form-inner input.valid { border-color: #22c55e; }
.auth-form-inner input.pw-error { border-color: #ef4444; }
.auth-btn { width: 100%; justify-content: center; padding: .75rem; font-size: 1rem; margin-top: .5rem; }
.auth-links { text-align: center; margin-top: 1.25rem; }
.auth-link { color: var(--primary-color, #3b82f6); text-decoration: none; font-size: .875rem; }
.auth-link:hover { text-decoration: underline; }

/* Password requirements */
.pw-requirements { display: none; margin-top: .5rem; padding: .6rem .75rem; background: rgba(59,130,246,.05); border-radius: 6px; border: 1px solid rgba(59,130,246,.15); }
.pw-requirements.show { display: block; }
.pw-req { font-size: .8rem; color: var(--text-muted, #6b7280); margin-bottom: .2rem; display: flex; align-items: center; gap: .4rem; transition: color .15s; }
.pw-req.met { color: #22c55e; }
.pw-req.unmet { color: #ef4444; }
.pw-req .pw-icon::before { content: '○'; }
.pw-req.met .pw-icon::before { content: '✓'; }
.pw-req.unmet .pw-icon::before { content: '✕'; }
#pw-match { font-size: .8rem; margin-top: .35rem; display: none; }
#pw-match.show { display: block; }
#pw-match.success { color: #22c55e; }
#pw-match.pw-error { color: #ef4444; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pwInput = document.getElementById('password');
    var confirmInput = document.getElementById('password_confirmation');
    var reqDiv = document.getElementById('password-requirements');
    var matchDiv = document.getElementById('pw-match');

    var rules = [
        { id: 'req-length',  regex: /.{8,}/ },
        { id: 'req-upper',   regex: /[A-Z]/ },
        { id: 'req-number',  regex: /[0-9]/ },
        { id: 'req-special', regex: /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>/?]/ },
    ];

    function checkPw() {
        var val = pwInput.value;
        if (!val) { reqDiv.classList.remove('show'); pwInput.classList.remove('valid','pw-error'); return; }
        reqDiv.classList.add('show');
        var allMet = true;
        rules.forEach(function (r) {
            var el = document.getElementById(r.id);
            var met = r.regex.test(val);
            el.classList.toggle('met', met);
            el.classList.toggle('unmet', !met);
            if (!met) allMet = false;
        });
        pwInput.classList.toggle('valid', allMet);
        pwInput.classList.toggle('pw-error', !allMet);
        checkMatch();
    }

    function checkMatch() {
        var confirm = confirmInput.value;
        if (!confirm) { matchDiv.classList.remove('show'); return; }
        matchDiv.classList.add('show');
        var ok = pwInput.value === confirm;
        matchDiv.textContent = ok
            ? '✓ <?= addslashes(trans('auth_passwords_match')) ?>'
            : '✕ <?= addslashes(trans('auth_passwords_not_match')) ?>';
        matchDiv.classList.toggle('success', ok);
        matchDiv.classList.toggle('pw-error', !ok);
    }

    pwInput.addEventListener('input', checkPw);
    confirmInput.addEventListener('input', checkMatch);
});

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
