<div class="login-wrapper">
    <div class="login-box">
        <h1 class="login-title">tombomeke</h1>
        <p class="login-subtitle">Sign in to the admin panel</p>

        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error['message']) ?></div>
        <?php endif; ?>

        <form method="POST" action="?page=admin&section=login">
            <?= \Auth::csrfField() ?>
            <div class="form-grid login-form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" autofocus autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary login-submit-btn">
                    <i class="fas fa-right-to-bracket"></i> Sign in
                </button>
            </div>
        </form>

        <p class="login-back-link">
            <a href="?page=home">← Back to site</a>
        </p>
    </div>
</div>
