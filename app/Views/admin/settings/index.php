<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<form method="POST" action="?page=admin&section=settings">
    <?= \Auth::csrfField() ?>

    <?php if (empty($settings)): ?>
        <div class="card">
            <p class="empty-state"><i class="fas fa-cog"></i> Nog geen instellingen. Voer <code>seed_site_settings.sql</code> uit in phpMyAdmin.</p>
        </div>
    <?php else: ?>

    <?php
    $settingModel = new SiteSettingModel();
    foreach ($settings as $group => $groupSettings):
        $icon = $settingModel->getGroupIcon($group);
    ?>
    <div class="card" style="margin-bottom:1rem">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-<?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars(ucfirst($group)) ?> Settings</span>
        </div>
        <div style="padding:1.25rem">
            <div class="form-grid" style="gap:1rem">
                <?php foreach ($groupSettings as $s): ?>
                <div class="form-group" style="border-bottom:1px solid var(--border);padding-bottom:1rem">
                    <label><?= htmlspecialchars($s['label']) ?></label>
                    <?php if ($s['description']): ?>
                        <span class="form-hint"><?= htmlspecialchars($s['description']) ?></span>
                    <?php endif; ?>

                    <?php if ($s['type'] === 'boolean'): ?>
                        <label style="display:flex;align-items:center;gap:.5rem;margin-top:.4rem;cursor:pointer">
                            <input type="checkbox" name="<?= htmlspecialchars($s['key']) ?>" value="1"
                                   <?= $s['typed_value'] ? 'checked' : '' ?>>
                            <span><?= $s['typed_value'] ? 'Ingeschakeld' : 'Uitgeschakeld' ?></span>
                        </label>
                    <?php elseif ($s['type'] === 'text' || strpos($s['key'], 'description') !== false): ?>
                        <textarea name="<?= htmlspecialchars($s['key']) ?>" rows="3"><?= htmlspecialchars($s['value'] ?? '') ?></textarea>
                    <?php elseif ($s['type'] === 'integer'): ?>
                        <input type="number" name="<?= htmlspecialchars($s['key']) ?>" value="<?= htmlspecialchars($s['value'] ?? '') ?>">
                    <?php else: ?>
                        <input type="text" name="<?= htmlspecialchars($s['key']) ?>" value="<?= htmlspecialchars($s['value'] ?? '') ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="form-actions" style="margin-top:.5rem">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Instellingen opslaan</button>
    </div>
    <?php endif; ?>
</form>
