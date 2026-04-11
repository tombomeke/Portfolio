<div class="login-wrapper">
    <div class="login-box">
        <h1>tombomeke</h1>
        <p>Sign in to the admin panel</p>

        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error['message']) ?></div>
        <?php endif; ?>

        <form method="POST" action="?page=admin&section=login">
            <?= \Auth::csrfField() ?>
            <div class="form-grid" style="gap:1rem">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" autofocus autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-right-to-bracket"></i> Sign in
                </button>
            </div>
        </form>

        <p style="margin-top:1.5rem;font-size:.8rem;text-align:center">
            <a href="?page=home" style="color:var(--text-muted)">← Back to site</a>
        </p>
    </div>
</div>
