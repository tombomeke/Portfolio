<?php if ($flash): ?>
    <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Alle projecten (<?= count($projects) ?>)</span>
        <div style="display:flex;gap:.4rem;align-items:center">
            <form method="POST" action="?page=admin&section=projects&action=sync-all"
                  onsubmit="return confirm('Alle project-roadmaps synchroniseren? Dit kan even duren.')">
                <?= \Auth::csrfField() ?>
                <button type="submit" class="btn btn-ghost btn-sm" title="Alle roadmaps syncen via ReadmeSync API">
                    <i class="fas fa-rotate"></i> Sync roadmaps
                </button>
            </form>
            <a href="?page=admin&section=projects&action=create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Toevoegen
            </a>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <p style="color:var(--text-muted)">Nog geen projecten. <a href="?page=admin&section=projects&action=create" style="color:var(--primary)">Voeg er een toe.</a></p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titel (NL)</th>
                    <th>Categorie</th>
                    <th>Status</th>
                    <th>Volgorde</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['title_nl'] ?? '—') ?></td>
                    <td><code style="font-size:.8rem"><?= htmlspecialchars($p['category']) ?></code></td>
                    <td>
                        <?php if ($p['status']): ?>
                            <span class="badge-status <?= htmlspecialchars($p['status']) ?>">
                                <?= htmlspecialchars($p['status']) ?>
                            </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= $p['sort_order'] ?></td>
                    <td style="display:flex;gap:.4rem">
                        <a href="?page=project&slug=<?= urlencode((string) ($p['slug'] ?? '')) ?>&tab=roadmap" class="btn btn-ghost btn-sm" title="Open publieke roadmap">
                            <i class="fas fa-list-check"></i>
                        </a>
                        <a href="?page=admin&section=projects&action=edit&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <form method="POST" action="?page=admin&section=projects&action=delete&id=<?= $p['id'] ?>" class="confirm-inline">
                            <?= \Auth::csrfField() ?>
                            <button type="submit" data-confirm="Project verwijderen?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php $syncLogs = $syncLogs ?? []; ?>
<div class="card" style="margin-top:1.5rem">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-rotate"></i> Roadmap sync logs</span>
        <span class="badge"><?= count($syncLogs) ?> recente entries</span>
    </div>
    <?php if (empty($syncLogs)): ?>
        <p style="color:var(--text-muted);padding:.75rem 0">
            Nog geen sync logs. Klik "Sync roadmaps" of open een project-detail met <code>?tab=roadmap&sync=1</code>.
        </p>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tijdstip</th>
                    <th>Project</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Contract</th>
                    <th>Foutmelding</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($syncLogs as $log): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:.8rem">
                        <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime((string) ($log['created_at'] ?? '')))) ?>
                    </td>
                    <td>
                        <?php if (!empty($log['project_slug'])): ?>
                            <a href="?page=project&slug=<?= urlencode((string) $log['project_slug']) ?>&tab=roadmap"
                               style="font-size:.85rem"><?= htmlspecialchars((string) ($log['project_title'] ?? 'Project #' . $log['project_id'])) ?></a>
                        <?php else: ?>
                            <span style="font-size:.85rem;color:var(--text-muted)">#<?= (int) ($log['project_id'] ?? 0) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) ($log['item_count'] ?? 0) ?></td>
                    <td>
                        <?php if ($log['success']): ?>
                            <span style="color:#22c55e;font-size:.8rem"><i class="fas fa-check-circle"></i> OK</span>
                        <?php else: ?>
                            <span style="color:#ef4444;font-size:.8rem"><i class="fas fa-times-circle"></i> Fout</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.75rem;color:var(--text-muted)"><?= htmlspecialchars((string) ($log['api_contract_version'] ?? '—')) ?></td>
                    <td style="font-size:.75rem;color:#ef4444;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars((string) ($log['error_message'] ?? '')) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
