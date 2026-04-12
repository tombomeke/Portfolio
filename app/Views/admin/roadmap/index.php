<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php $defaultRepoUrl = !empty($config['repoUrl']) ? (string) $config['repoUrl'] : 'https://github.com/tombomeke/Portfolio'; ?>
<?php $defaultMarkdownSource = !empty($config['markdownSource']) ? (string) $config['markdownSource'] : "# Roadmap\n\n- [ ] [P1] Voeg item 1 toe\n- [ ] [low] Voeg item 2 toe\n- [x] Reeds afgewerkt item\n- [ ] TODO: extra sync test in checkbox-vorm\n\n## TODO\n\n- Extra taak zonder checkbox (fallback naar todo)\n# TODO: Nog een taak als compacte TODO-regel\n- [TODO] Nog een testregel met bracket-syntax\n- Fix navbar op mobiel TODO: toevoegen aan roadmap\n- Nog een task"; ?>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-pen-to-square"></i> Roadmap markdown (direct beheren)</span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:1rem">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="sync_markdown">

        <div class="form-group">
            <label>Roadmap markdown</label>
            <textarea id="roadmap-markdown-source" name="markdown_source" class="form-input" rows="12" style="width:100%;font-family:Consolas,monospace" required><?= htmlspecialchars($defaultMarkdownSource) ?></textarea>
            <span class="form-hint">Deze markdown wordt direct gebruikt om roadmap-items op deze website bij te werken. Geen README of extern .md bestand nodig.</span>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input id="roadmap-todos-only" type="checkbox" name="todos_only" value="1" checked>
                <span>Importeer alleen TODO items (onafgevinkt)</span>
            </label>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="merge_mode" value="1" checked>
                <span>Merge mode: behoud bestaande items en update gematchte titels</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-rotate"></i> Sync roadmap vanuit tekst</button>
        </div>
    </form>
</div>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-magnifying-glass"></i> Preview gevonden roadmap items</span>
        <span class="badge" id="roadmap-preview-count">0 items</span>
    </div>
    <p class="text-muted text-sm" style="margin:0 0 .75rem">Live preview op basis van de markdown hierboven. Zo zie je meteen wat er gesynced wordt.</p>
    <div id="roadmap-preview-list" style="display:grid;gap:.55rem"></div>
</div>

<div class="card" style="margin-bottom:1rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-wand-magic-sparkles"></i> Optioneel: Sync vanuit ReadmeSync URL</span>
    </div>
    <form method="POST" action="?page=admin&section=roadmap" class="form-grid" style="gap:1rem">
        <?= \Auth::csrfField() ?>
        <input type="hidden" name="roadmap_action" value="sync">

        <div class="form-group">
            <label>GitHub repository URL</label>
            <input type="url" name="repo_url" placeholder="https://github.com/owner/repo" value="<?= htmlspecialchars($defaultRepoUrl) ?>" required>
            <span class="form-hint">Leest items uit de ReadmeSync output. Ondersteunt checkboxes, bullets en TODO-regels zoals <strong># TODO:</strong>. Bij aanwezigheid van een Roadmap- of TODO-sectie wordt alleen die sectie gesynchroniseerd.</span>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="todos_only" value="1" checked>
                <span>Importeer alleen TODO items (onafgevinkt)</span>
            </label>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="merge_mode" value="1" checked>
                <span>Merge mode: behoud bestaande items en update gematchte titels</span>
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
                        <?php if (!empty($item['priority']) && $item['priority'] !== 'normal'): ?>
                            <small class="text-muted" style="display:inline-block;margin-top:.2rem">Prioriteit: <?= htmlspecialchars((string) $item['priority']) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item['sourceLine'])): ?>
                            <small class="text-muted" style="display:block">Bronregel: #<?= (int) $item['sourceLine'] ?><?= !empty($item['sourceSection']) ? ' · Sectie: ' . htmlspecialchars((string) $item['sourceSection']) : '' ?></small>
                        <?php endif; ?>
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
        <span class="card-title"><i class="fas fa-circle-info"></i> Roadmap bron</span>
    </div>
    <p class="text-muted text-sm" style="margin:0">
        Deze roadmap wordt gedeeld met de website via <code>app/Config/roadmap_items.json</code>. Alles wat je hierboven synced komt direct op de publieke roadmap terecht.
    </p>
</div>

<script>
(() => {
    const markdownInput = document.getElementById('roadmap-markdown-source');
    const todosOnlyInput = document.getElementById('roadmap-todos-only');
    const previewCount = document.getElementById('roadmap-preview-count');
    const previewList = document.getElementById('roadmap-preview-list');

    if (!markdownInput || !todosOnlyInput || !previewCount || !previewList) {
        return;
    }

    const normalizeTitle = (value) => {
        return (value || '')
            .toLowerCase()
            .replace(/\[(p1|p2|p3|high|medium|med|low)\]/gi, '')
            .replace(/\s+/g, ' ')
            .replace(/[^a-z0-9 ]/g, '')
            .trim();
    };

    const parsePriority = (value) => {
        const match = (value || '').match(/\[(p1|p2|p3|high|medium|med|low)\]/i);
        if (!match) {
            return 'normal';
        }
        const token = match[1].toLowerCase();
        if (token === 'p1' || token === 'high') return 'high';
        if (token === 'p2' || token === 'medium' || token === 'med') return 'medium';
        if (token === 'p3' || token === 'low') return 'low';
        return 'normal';
    };

    const cleanupTitle = (value) => {
        return (value || '').replace(/\[(p1|p2|p3|high|medium|med|low)\]/gi, '').trim();
    };

    const parseLine = (line) => {
        const trimmed = line.trim();
        if (!trimmed) return null;

        let match = line.match(/^\s*[-*]\s*\[( |x|X)\]\s+(.+)$/);
        if (match) {
            const rawTitle = (match[2] || '').trim();
            if (!rawTitle) return null;
            return {
                status: (match[1] || '').trim().toLowerCase() === 'x' ? 'done' : 'todo',
                title: cleanupTitle(rawTitle),
                priority: parsePriority(rawTitle),
            };
        }

        match = line.match(/^\s*(?:[-*]|\d+\.)\s+(.+)$/);
        if (match) {
            const rawTitle = (match[1] || '').trim();
            if (!rawTitle) return null;
            return { status: 'todo', title: cleanupTitle(rawTitle), priority: parsePriority(rawTitle) };
        }

        match = trimmed.match(/^\s*(?:[#>\-*]\s*)?(?:TODO|TO DO)\s*[:\-]\s*(.+)$/i);
        if (match) {
            const rawTitle = (match[1] || '').trim();
            if (!rawTitle) return null;
            return { status: 'todo', title: cleanupTitle(rawTitle), priority: parsePriority(rawTitle) };
        }

        match = trimmed.match(/^\s*\[\s*(?:TODO|TO DO)\s*\]\s*(.+)$/i);
        if (match) {
            const rawTitle = (match[1] || '').trim();
            if (!rawTitle) return null;
            return { status: 'todo', title: cleanupTitle(rawTitle), priority: parsePriority(rawTitle) };
        }

        match = trimmed.match(/\b(?:TODO|TO DO)\b\s*[:\-]\s*(.+)$/i);
        if (match) {
            const rawTitle = (match[1] || '').trim();
            if (!rawTitle) return null;
            return { status: 'todo', title: cleanupTitle(rawTitle), priority: parsePriority(rawTitle) };
        }

        return null;
    };

    const collectTargetLines = (lines) => {
        const sectionLines = [];
        let activeSection = null;
        let foundTargetSection = false;

        lines.forEach((line, index) => {
            const text = (line || '').trim();
            const headingMatch = text.match(/^#{1,6}\s*(.+?)\s*$/);
            if (headingMatch) {
                const heading = (headingMatch[1] || '').trim();
                if (/^(roadmap|todo)(?:\b|\s*[:\-].*)?$/i.test(heading)) {
                    activeSection = heading.toLowerCase().replace(/\s*[:\-].*$/, '');
                    foundTargetSection = true;
                } else {
                    activeSection = null;
                }
                return;
            }

            if (activeSection) {
                sectionLines.push({ line, lineNumber: index + 1, section: activeSection });
            }
        });

        if (foundTargetSection) return sectionLines;
        return lines.map((line, index) => ({ line, lineNumber: index + 1, section: '' }));
    };

    const renderPreview = () => {
        const lines = (markdownInput.value || '').split(/\r?\n/);
        const targetLines = collectTargetLines(lines);
        const items = [];
        const seen = new Set();

        targetLines.forEach((entry) => {
            const parsed = parseLine(entry.line || '');
            if (!parsed) return;
            if (todosOnlyInput.checked && parsed.status === 'done') return;

            const key = normalizeTitle(parsed.title);
            if (!key || seen.has(key)) return;
            seen.add(key);

            items.push({
                ...parsed,
                lineNumber: entry.lineNumber,
                section: entry.section,
            });
        });

        previewCount.textContent = `${items.length} items`;
        if (items.length === 0) {
            previewList.innerHTML = '<p class="text-muted" style="margin:0">Geen items gevonden met de huidige markdown/filter.</p>';
            return;
        }

        previewList.innerHTML = items.map((item) => {
            const isDone = item.status === 'done';
            const priorityPart = item.priority && item.priority !== 'normal'
                ? `<small class="text-muted" style="display:block">Prioriteit: ${item.priority}</small>`
                : '';
            const sectionPart = item.section ? ` · Sectie: ${item.section}` : '';
            return `
                <div style="padding:.65rem .8rem;border:1px solid var(--border);border-radius:8px;background:${isDone ? 'rgba(34,197,94,.06)' : 'rgba(245,158,11,.05)'}">
                    <strong style="display:block">${item.title.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</strong>
                    ${priorityPart}
                    <small class="text-muted" style="display:block">Status: ${item.status}${sectionPart} · regel #${item.lineNumber}</small>
                </div>
            `;
        }).join('');
    };

    markdownInput.addEventListener('input', renderPreview);
    todosOnlyInput.addEventListener('change', renderPreview);
    renderPreview();
})();
</script>
