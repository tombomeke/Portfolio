<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="display:flex;gap:1rem;align-items:flex-start">
    <div style="flex:1">
        <div class="card" style="margin-bottom:1rem">
            <div class="card-header">
                <span class="card-title"><?= trans('admin_message_from') ?> <?= htmlspecialchars($message['name']) ?></span>
                <a href="?page=admin&section=contact" class="btn btn-ghost btn-sm">← <?= trans('admin_back') ?></a>
            </div>

            <div style="display:grid;gap:.5rem;margin-bottom:1.5rem;font-size:.875rem">
                <div><strong><?= trans('admin_table_from') ?>:</strong> <?= htmlspecialchars($message['name']) ?></div>
                <div><strong><?= trans('contact_email') ?>:</strong> <a href="mailto:<?= htmlspecialchars($message['email']) ?>" style="color:var(--primary)"><?= htmlspecialchars($message['email']) ?></a></div>
                <?php if ($message['subject']): ?>
                <div><strong><?= trans('admin_table_subject') ?>:</strong> <?= htmlspecialchars($message['subject']) ?></div>
                <?php endif; ?>
                <div><strong><?= trans('admin_received_label') ?>:</strong> <?= date('d-m-Y H:i', strtotime($message['created_at'])) ?></div>
            </div>

            <div style="background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:1.25rem;white-space:pre-wrap;line-height:1.7;font-size:.9rem"><?= htmlspecialchars($message['message']) ?></div>
        </div>

        <?php if ($message['admin_reply']): ?>
        <div class="card" style="margin-bottom:1rem;border-color:rgba(34,197,94,.3)">
            <div class="card-header">
                <span class="card-title" style="color:var(--success)"><i class="fas fa-reply"></i> <?= trans('admin_your_reply') ?></span>
                <small style="color:var(--text-muted)"><?= date('d-m-Y H:i', strtotime($message['replied_at'])) ?></small>
            </div>
            <div style="white-space:pre-wrap;font-size:.9rem;line-height:1.7"><?= htmlspecialchars($message['admin_reply']) ?></div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <span class="card-title"><?= $message['admin_reply'] ? trans('admin_reply_again') : trans('admin_reply') ?></span>
            </div>
            <form method="POST" action="?page=admin&section=contact&action=reply&id=<?= $message['id'] ?>">
                <?= \Auth::csrfField() ?>
                <div class="form-group" style="margin-bottom:1rem">
                    <label><?= trans('admin_message_to') ?> <?= htmlspecialchars($message['email']) ?></label>
                    <textarea name="reply" class="tall" required placeholder="<?= trans('admin_write_reply_placeholder') ?>"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?= trans('admin_send_save') ?></button>
                </div>
            </form>
        </div>
    </div>

    <div style="width:240px;flex-shrink:0">
        <div class="card">
            <div class="card-title" style="margin-bottom:1rem"><?= trans('admin_actions') ?></div>
            <form method="POST" action="?page=admin&section=contact&action=delete&id=<?= $message['id'] ?>">
                <?= \Auth::csrfField() ?>
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center" data-confirm="<?= trans('admin_message_delete_confirm') ?>">
                    <i class="fas fa-trash"></i> <?= trans('admin_action_delete') ?>
                </button>
            </form>
        </div>
    </div>
</div>
