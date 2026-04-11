<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php $defaultRepoUrl = !empty($config['repoUrl']) ? (string) $config['repoUrl'] : 'https://github.com/tombomeke/Portfolio'; ?>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-wand-magic-sparkles"></i> Sync vanuit ReadmeSync</span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:1rem">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="sync">

        <div class="form-group">
            <label>GitHub repository URL</label>
            <input type="url" name="repo_url" placeholder="https://github.com/owner/repo" value="<?= htmlspecialchars($defaultRepoUrl) ?>" required>
            <span class="form-hint">Leest checklist-items uit ReadmeSync output. Gebruik markdown checkboxes: - [ ] en - [x].</span>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="todos_only" value="1" checked>
                <span>Importeer alleen TODO items (onafgevinkt)</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-rotate"></i> Sync roadmap</button>
        </div>
    </form>
</div>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-list-check"></i> Roadmap items</span>
        <span class="badge"><?= count((array) ($config['items'] ?? [])) ?> items</span>
    </div>

    <?php if (!empty($config['lastSyncAt'])): ?>
        <p class="text-muted text-sm" style="margin:0 0 1rem">
            Laatste sync: <?= date('d/m/Y H:i', strtotime((string) $config['lastSyncAt'])) ?>
            · bron: <?= htmlspecialchars((string) ($config['source'] ?? 'manual')) ?>
        </p>
    <?php endif; ?>

    <?php if (empty($config['items'])): ?>
        <p class="empty-state"><i class="fas fa-inbox"></i> Geen roadmap items gevonden.</p>
    <?php else: ?>
        <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:.75rem">
            <?= \Auth::csrfField() ?>
            <input type="hidden" name="roadmap_action" value="save">

            <?php foreach ($config['items'] as $item): ?>
                <?php $id = (string) ($item['id'] ?? ''); ?>
                <?php $isDone = (($item['status'] ?? 'todo') === 'done'); ?>
                <label style="display:flex;align-items:flex-start;gap:.75rem;padding:.7rem .85rem;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:<?= $isDone ? 'rgba(34,197,94,.06)' : 'rgba(245,158,11,.05)' ?>">
                    <input type="checkbox" name="done[]" value="<?= htmlspecialchars($id) ?>" <?= $isDone ? 'checked' : '' ?>>
                    <span>
                        <strong style="display:block"><?= htmlspecialchars((string) ($item['title'] ?? 'Roadmap item')) ?></strong>
                        <?php if (!empty($item['description'])): ?>
                            <small class="text-muted"><?= htmlspecialchars((string) $item['description']) ?></small>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Status opslaan</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-arrow-rotate-left"></i> Reset</span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" onsubmit="return confirm('Roadmap terugzetten naar standaarditems?');">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="reset">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Reset naar defaults</button>
    </form>
</div>

<div class="card" style="margin-top:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-lightbulb"></i> TODO verbeteringen</span>
    </div>
    <ul style="margin-left:1.1rem;display:grid;gap:.35rem;color:var(--text-muted)">
        <li>Section-targeted parsing: sync alleen uit README sectie "Roadmap" of "TODO".</li>
        <li>Merge mode: bewaar handmatige items en werk alleen gematchte synced items bij.</li>
        <li>Prioriteit parsing: herken tags zoals [P1], [P2], [low].</li>
        <li>Traceability: sla source line nummers op voor debug links naar bronregels.</li>
    </ul>
</div>

<div class="card" style="margin-top:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-file-lines"></i> Roadmap markdown template</span>
    </div>
    <p class="text-muted text-sm" style="margin:0 0 .75rem">
        Gebruik dit formaat om checklist-items snel te copy-pasten in je README. De sync herkent zowel checkboxes als gewone bullets.
    </p>
    <textarea id="roadmap-template" class="form-input" rows="10" style="width:100%;font-family:Consolas,monospace">## Roadmap

- [ ] Voeg item 1 toe
- [ ] Voeg item 2 toe
- [x] Reeds afgewerkt item

## TODO

- Extra taak zonder checkbox (fallback naar todo)
- Nog een task
</textarea>
    <div class="form-actions" style="margin-top:.75rem">
        <button type="button" id="copy-roadmap-template" class="btn btn-ghost"><i class="fas fa-copy"></i> Copy template</button>
        <button type="button" id="download-roadmap-template" class="btn btn-ghost"><i class="fas fa-download"></i> Download .md</button>
    </div>
</div>

<script>
(() => {
    const template = document.getElementById('roadmap-template');
    const copyBtn = document.getElementById('copy-roadmap-template');
    const downloadBtn = document.getElementById('download-roadmap-template');
    if (!template || !copyBtn || !downloadBtn) return;

    copyBtn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(template.value);
            copyBtn.textContent = 'Gekopieerd';
            setTimeout(() => { copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy template'; }, 1300);
        } catch (_) {
            template.select();
            document.execCommand('copy');
        }
    });

    downloadBtn.addEventListener('click', () => {
        const blob = new Blob([template.value], { type: 'text/markdown;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'roadmap-template.md';
        a.click();
        URL.revokeObjectURL(url);
    });
})();
</script>
