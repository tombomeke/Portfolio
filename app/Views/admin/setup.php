<div class="login-wrapper">
    <div class="login-box" style="max-width:460px">
        <h1>First-time setup</h1>
        <p>Create your owner account. This page is only accessible when no users exist.</p>

        <?php if ($error): ?>
            <div class="flash error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="?page=setup" style="margin-top:1.5rem">
            <div class="form-grid" style="gap:1rem">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="tombomeke" autofocus required pattern="[a-zA-Z0-9_]{3,30}">
                    <span class="form-hint">3–30 characters. Letters, numbers, underscore.</span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <span class="form-hint">Minimum 8 characters.</span>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-crown"></i> Create owner account
                </button>
            </div>
        </form>
    </div>
</div>
