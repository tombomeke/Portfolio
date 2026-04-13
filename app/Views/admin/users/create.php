<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card" style="max-width:500px">
    <div class="card-header">
        <span class="card-title"><?= trans('admin_users_add_admin') ?></span>
        <a href="?page=admin&section=users" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
    </div>

    <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1.5rem">
        <?= trans('admin_users_create_note') ?>
    </p>

    <form method="POST" action="?page=admin&section=users&action=create">
        <?= \Auth::csrfField() ?>
        <div class="form-grid" style="gap:1.25rem">
            <div class="form-group">
                <label><?= trans('admin_users_username_required') ?></label>
                <input type="text" name="username" required pattern="[a-zA-Z0-9_]{3,30}" autofocus>
                <span class="form-hint"><?= trans('admin_users_username_hint') ?></span>
            </div>
            <div class="form-group">
                <label><?= trans('admin_users_email_required') ?></label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label><?= trans('admin_users_password_required') ?></label>
                <input type="password" name="password" required minlength="8">
                <span class="form-hint"><?= trans('admin_users_password_hint') ?></span>
            </div>
            <div class="form-group">
                <label><?= trans('admin_users_confirm_password_required') ?></label>
                <input type="password" name="confirm" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> <?= trans('auth_register_title') ?></button>
                <a href="?page=admin&section=users" class="btn btn-ghost"><?= trans('admin_cancel') ?></a>
            </div>
        </div>
    </form>
</div>
