<section class="readmesync-page">
    <div class="container">

        <div class="readmesync-header">
            <h1>ReadmeSync <span class="badge-live">Live</span></h1>
            <p class="lead">
                Vul een publieke GitHub repo-URL in om direct een live code-overzicht te genereren —
                inclusief namespaces, klassen, methoden en open TO-DOs.
                Aangedreven door <strong>ReadmeSync</strong>, gemaakt door Tombomeke Studios.
            </p>
        </div>

        <?php if (!isset($_SESSION['auth_user'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-lock"></i>
            Om een readme te genereren moet je
            <a href="?page=login&redirect=<?= urlencode('?page=readmesync') ?>">inloggen</a>
            of <a href="?page=register">registreren</a> — het bekijken van de pagina is gratis.
        </div>
        <?php endif; ?>

        <?php if (!$repoUrl): ?>
        <div class="readmesync-quicklinks">
            <h2>Mijn projecten</h2>
            <p>Klik om een live overzicht te laden:</p>
            <ul class="quicklink-list">
                <li>
                    <a href="?page=readmesync&amp;repo=https://github.com/tombomeke-ehb/ReadmeSync" class="btn btn-outline">
                        tombomeke-ehb / ReadmeSync
                    </a>
                </li>
                <li>
                    <a href="?page=readmesync&amp;repo=https://github.com/tombomeke-ehb/ReadmeSync.API" class="btn btn-outline">
                        tombomeke-ehb / ReadmeSync.API
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>

        <form method="GET" action="?" class="readmesync-form">
            <input type="hidden" name="page" value="readmesync">
            <div class="readmesync-input-group">
                <input
                    type="url"
                    name="repo"
                    value="<?= $repoUrl ?>"
                    placeholder="https://github.com/owner/repo"
                    class="form-input"
                    required
                    autocomplete="url"
                />
                <button type="submit" class="btn btn-primary">Genereer</button>
            </div>
        </form>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php if (!empty($_SESSION['auth_user']) && in_array(($_SESSION['auth_user']['role'] ?? ''), ['owner', 'admin'], true)): ?>
        <details class="readmesync-debug">
            <summary>Debug details (admin)</summary>
            <div class="debug-grid">
                <div><strong>HTTP code:</strong> <?= (int)($debugHttpCode ?? 0) ?></div>
                <div><strong>cURL error:</strong> <?= htmlspecialchars((string)($debugCurlErr ?? 'none'), ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Body snippet:</strong></div>
                <pre><?= htmlspecialchars((string)($debugRawBody ?? 'empty'), ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
        </details>
        <?php endif; ?>
        <?php if ($debugHttpCode): ?>
        <!-- [ReadmeSync debug] http_code: <?= $debugHttpCode ?> | curl_error: <?= $debugCurlErr ?? 'none' ?> | body: <?= $debugRawBody ?? 'empty' ?> -->
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($result): ?>
        <div class="readmesync-result">
            <div class="result-meta">
                <span><strong>Taal:</strong> <?= htmlspecialchars(strtoupper($language ?? '?'), ENT_QUOTES, 'UTF-8') ?></span>
                &nbsp;&middot;&nbsp;
                <a href="<?= htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                    Bekijk op GitHub &#8599;
                </a>
            </div>

            <?php if (!empty($_SESSION['auth_user']) && (($_SESSION['auth_user']['role'] ?? '') === 'owner')): ?>
            <details class="readmesync-debug" style="margin-bottom:.9rem">
                <summary>Debug details (owner)</summary>
                <div class="debug-grid">
                    <div><strong>Payload source_user_id:</strong> <?= htmlspecialchars((string) ($debugSourceUserId ?? 'null'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>Payload source_user_name:</strong> <?= htmlspecialchars((string) ($debugSourceUserName ?? 'null'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>API contract version:</strong> <?= htmlspecialchars((string) ($debugApiContractVersion ?? 'none'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong>Response keys:</strong> <?= htmlspecialchars(implode(', ', (array) ($debugResponseKeys ?? [])), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </details>
            <?php endif; ?>

            <pre><code><?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8') ?></code></pre>
        </div>
        <?php endif; ?>

    </div>
</section>

<style>
.readmesync-page { padding: 4rem 0; }
.readmesync-header { margin-bottom: 2rem; }
.readmesync-header h1 { display: flex; align-items: center; gap: .6rem; }
.badge-live {
    font-size: .55em; font-weight: 600; padding: .2em .55em;
    background: #22c55e; color: #fff; border-radius: 4px;
    vertical-align: middle; letter-spacing: .03em;
}
.readmesync-quicklinks { margin-bottom: 2rem; }
.quicklink-list { list-style: none; padding: 0; display: flex; gap: 1rem; flex-wrap: wrap; }
.readmesync-form { margin-bottom: 1.5rem; }
.readmesync-input-group { display: flex; gap: .75rem; flex-wrap: wrap; }
.readmesync-input-group .form-input {
    flex: 1; min-width: 280px; padding: .65rem 1rem;
    border: 1px solid var(--border-color, #d1d5db); border-radius: 6px;
    font-size: 1rem;
}
.alert { padding: .85rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; }
.alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.alert-info  { background: rgba(59,130,246,.06); color: var(--primary-color,#1d4ed8); border: 1px solid rgba(59,130,246,.25); }
.alert-info a { color: inherit; font-weight: 600; }
.readmesync-debug { margin-top: -0.5rem; margin-bottom: 1.2rem; border: 1px dashed rgba(148,163,184,.45); border-radius: 8px; padding: .7rem .9rem; }
.readmesync-debug summary { cursor: pointer; font-weight: 600; color: var(--text-secondary,#cbd5e1); margin-bottom: .55rem; }
.readmesync-debug .debug-grid { display: grid; gap: .45rem; color: var(--text-muted,#94a3b8); font-size: .88rem; }
.readmesync-debug pre { white-space: pre-wrap; word-break: break-word; background: rgba(15,23,42,.75); border-radius: 6px; padding: .6rem; max-height: 220px; overflow:auto; }
.readmesync-result { margin-top: 1.5rem; }
.result-meta {
    display: flex; align-items: center; gap: .5rem; font-size: .9rem;
    color: var(--text-muted, #6b7280); margin-bottom: .75rem;
}
.readmesync-result pre {
    background: #0f172a; color: #e2e8f0;
    padding: 1.5rem; border-radius: 8px;
    overflow-x: auto; font-size: .82rem; line-height: 1.6;
    max-height: 70vh;
}
.readmesync-result code { font-family: 'Courier New', Courier, monospace; }
</style>
