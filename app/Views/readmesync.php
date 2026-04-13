<section class="readmesync-page">
    <div class="container">

        <div class="readmesync-header">
            <h1>ReadmeSync <span class="badge-live">Live</span></h1>
            <p class="lead">
                <?= trans('readmesync_intro') ?>
            </p>
        </div>

        <?php if (!isset($_SESSION['auth_user'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-lock"></i>
            <?= trans('readmesync_login_notice_prefix') ?>
            <a href="?page=login&redirect=<?= urlencode('?page=readmesync') ?>"><?= trans('nav_login') ?></a>
            <?= trans('readmesync_login_notice_or') ?> <a href="?page=register"><?= trans('readmesync_register_link') ?></a> <?= trans('readmesync_login_notice_suffix') ?>
        </div>
        <?php endif; ?>

        <?php if (!$repoUrl): ?>
        <div class="readmesync-quicklinks">
            <h2><?= trans('readmesync_my_projects') ?></h2>
            <p><?= trans('readmesync_click_quickload') ?></p>
            <div class="quicklink-grid">
                <a href="?page=readmesync&amp;repo=https://github.com/tombomeke-ehb/ReadmeSync" class="quicklink-card">
                    <i class="fab fa-github quicklink-icon"></i>
                    <div>
                        <strong>ReadmeSync</strong>
                        <span>tombomeke-ehb / ReadmeSync</span>
                    </div>
                </a>
                <a href="?page=readmesync&amp;repo=https://github.com/tombomeke-ehb/ReadmeSync.API" class="quicklink-card">
                    <i class="fab fa-github quicklink-icon"></i>
                    <div>
                        <strong>ReadmeSync.API</strong>
                        <span>tombomeke-ehb / ReadmeSync.API</span>
                    </div>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <form method="GET" action="?" class="readmesync-form" id="readmesync-form">
            <input type="hidden" name="page" value="readmesync">
            <div class="readmesync-input-group">
                <input
                    type="url"
                    name="repo"
                    value="<?= htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://github.com/owner/repo"
                    class="form-input"
                    required
                    autocomplete="url"
                    id="readmesync-url-input"
                />
                <button type="submit" class="btn btn-primary" id="readmesync-submit">
                    <i class="fas fa-wand-magic-sparkles"></i> <?= trans('readmesync_generate') ?>
                </button>
            </div>
        </form>

        <!-- Loading overlay (shown during API call) -->
        <div class="readmesync-loading" id="readmesync-loading" style="display:none">
            <div class="readmesync-loading-inner">
                <div class="readmesync-spinner"></div>
                <p><?= trans('readmesync_loading') ?></p>
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php if (!empty($_SESSION['auth_user']) && in_array(($_SESSION['auth_user']['role'] ?? ''), ['owner', 'admin'], true)): ?>
        <details class="readmesync-debug">
            <summary><?= trans('readmesync_debug_admin') ?></summary>
            <div class="debug-grid">
                <div><strong><?= trans('readmesync_http_code') ?>:</strong> <?= (int)($debugHttpCode ?? 0) ?></div>
                <div><strong><?= trans('readmesync_curl_error') ?>:</strong> <?= htmlspecialchars((string)($debugCurlErr ?? 'none'), ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong><?= trans('readmesync_body_snippet') ?>:</strong></div>
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
                <span><strong><?= trans('readmesync_language') ?>:</strong> <?= htmlspecialchars(strtoupper($language ?? '?'), ENT_QUOTES, 'UTF-8') ?></span>
                &nbsp;&middot;&nbsp;
                <a href="<?= htmlspecialchars($repoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                    <?= trans('readmesync_view_github') ?> &#8599;
                </a>
                &nbsp;&middot;&nbsp;
                <button type="button" class="btn-copy" id="readmesync-copy" title="<?= trans('readmesync_copy_title') ?>">
                    <i class="fas fa-copy"></i> <?= trans('readmesync_copy') ?>
                </button>
            </div>

            <?php if (!empty($_SESSION['auth_user']) && (($_SESSION['auth_user']['role'] ?? '') === 'owner')): ?>
            <details class="readmesync-debug" style="margin-bottom:.9rem">
                <summary><?= trans('readmesync_debug_owner') ?></summary>
                <div class="debug-grid">
                    <div><strong><?= trans('readmesync_payload_user_id') ?>:</strong> <?= htmlspecialchars((string) ($debugSourceUserId ?? 'null'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong><?= trans('readmesync_payload_user_name') ?>:</strong> <?= htmlspecialchars((string) ($debugSourceUserName ?? 'null'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong><?= trans('readmesync_api_contract_version') ?>:</strong> <?= htmlspecialchars((string) ($debugApiContractVersion ?? 'none'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div><strong><?= trans('readmesync_response_keys') ?>:</strong> <?= htmlspecialchars(implode(', ', (array) ($debugResponseKeys ?? [])), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </details>
            <?php endif; ?>

            <div class="result-code-wrap">
                <pre id="readmesync-output"><code><?= htmlspecialchars($result, ENT_QUOTES, 'UTF-8') ?></code></pre>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<script>
(function () {
    // Loading state on form submit
    const form    = document.getElementById('readmesync-form');
    const loading = document.getElementById('readmesync-loading');
    const submit  = document.getElementById('readmesync-submit');
    if (form && loading && submit) {
        form.addEventListener('submit', function () {
            submit.disabled = true;
            submit.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <?= addslashes(trans('readmesync_busy')) ?>';
            loading.style.display = 'flex';
        });
    }

    // Copy-to-clipboard
    const copyBtn = document.getElementById('readmesync-copy');
    const output  = document.getElementById('readmesync-output');
    if (copyBtn && output) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(output.textContent || '').then(function () {
                copyBtn.innerHTML = '<i class="fas fa-check"></i> <?= addslashes(trans('copied')) ?>';
                setTimeout(function () {
                    copyBtn.innerHTML = '<i class="fas fa-copy"></i> <?= addslashes(trans('readmesync_copy')) ?>';
                }, 2000);
            });
        });
    }
})();
</script>

<style>
.readmesync-page { padding: 4rem 0; }
.readmesync-header { margin-bottom: 2rem; }
.readmesync-header h1 { display: flex; align-items: center; gap: .6rem; }
.badge-live {
    font-size: .55em; font-weight: 600; padding: .2em .55em;
    background: #22c55e; color: #fff; border-radius: 4px;
    vertical-align: middle; letter-spacing: .03em;
}

/* Quicklinks */
.readmesync-quicklinks { margin-bottom: 2rem; }
.quicklink-grid { display: flex; gap: 1rem; flex-wrap: wrap; }
.quicklink-card {
    display: flex; align-items: center; gap: .75rem;
    padding: .85rem 1.2rem; border: 1px solid var(--border-color);
    border-radius: 10px; background: var(--surface-color);
    text-decoration: none; color: var(--text-primary);
    transition: border-color .15s, box-shadow .15s;
    flex: 1; min-width: 220px;
}
.quicklink-card:hover {
    border-color: var(--primary, #4f46e5);
    box-shadow: 0 0 0 2px rgba(79,70,229,.12);
}
.quicklink-icon { font-size: 1.5rem; color: var(--text-muted); flex-shrink: 0; }
.quicklink-card div { display: flex; flex-direction: column; gap: .1rem; }
.quicklink-card strong { font-size: .95rem; }
.quicklink-card span { font-size: .8rem; color: var(--text-muted); }

/* Form */
.readmesync-form { margin-bottom: 1.5rem; }
.readmesync-input-group { display: flex; gap: .75rem; flex-wrap: wrap; }
.readmesync-input-group .form-input {
    flex: 1; min-width: 280px; padding: .65rem 1rem;
    border: 1px solid var(--border-color, #d1d5db); border-radius: 6px;
    background: var(--surface-color); color: var(--text-primary);
    font-size: 1rem;
}

/* Loading overlay */
.readmesync-loading {
    display: flex; justify-content: center; align-items: center;
    padding: 2.5rem 0; margin-bottom: 1rem;
}
.readmesync-loading-inner {
    display: flex; flex-direction: column; align-items: center; gap: .75rem;
    color: var(--text-muted); font-size: .9rem;
}
.readmesync-spinner {
    width: 36px; height: 36px;
    border: 3px solid var(--border-color);
    border-top-color: var(--primary, #4f46e5);
    border-radius: 50%;
    animation: rs-spin .7s linear infinite;
}
@keyframes rs-spin { to { transform: rotate(360deg); } }

/* Alerts */
.alert { padding: .85rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; gap: .6rem; align-items: flex-start; }
.alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.alert-info  { background: rgba(59,130,246,.06); color: var(--primary-color,#1d4ed8); border: 1px solid rgba(59,130,246,.25); }
.alert-info a { color: inherit; font-weight: 600; }

/* Debug */
.readmesync-debug { margin-top: -0.5rem; margin-bottom: 1.2rem; border: 1px dashed rgba(148,163,184,.45); border-radius: 8px; padding: .7rem .9rem; }
.readmesync-debug summary { cursor: pointer; font-weight: 600; color: var(--text-secondary,#cbd5e1); margin-bottom: .55rem; }
.readmesync-debug .debug-grid { display: grid; gap: .45rem; color: var(--text-muted,#94a3b8); font-size: .88rem; }
.readmesync-debug pre { white-space: pre-wrap; word-break: break-word; background: rgba(15,23,42,.75); border-radius: 6px; padding: .6rem; max-height: 220px; overflow:auto; }

/* Result */
.readmesync-result { margin-top: 1.5rem; }
.result-meta {
    display: flex; align-items: center; gap: .5rem; font-size: .9rem;
    color: var(--text-muted, #6b7280); margin-bottom: .75rem; flex-wrap: wrap;
}
.result-code-wrap { position: relative; }
.readmesync-result pre {
    background: #0f172a; color: #e2e8f0;
    padding: 1.5rem; border-radius: 8px;
    overflow-x: auto; font-size: .82rem; line-height: 1.6;
    max-height: 70vh;
}
.readmesync-result code { font-family: 'Courier New', Courier, monospace; }

/* Copy button */
.btn-copy {
    background: none; border: 1px solid var(--border-color); border-radius: 5px;
    color: var(--text-muted); padding: .2rem .6rem; font-size: .8rem;
    cursor: pointer; transition: all .15s;
}
.btn-copy:hover { background: var(--primary, #4f46e5); color: #fff; border-color: var(--primary, #4f46e5); }
</style>
