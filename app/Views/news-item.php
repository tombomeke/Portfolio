<section class="news-item-page">
    <div class="container container--narrow">

        <a href="?page=news" class="back-link">&larr; Terug naar News</a>

        <article>
            <header class="news-item-header">
                <?php if ($item['image_path']): ?>
                <img src="<?= htmlspecialchars($item['image_path']) ?>"
                     alt="<?= htmlspecialchars($item['title']) ?>"
                     class="news-item-image">
                <?php endif; ?>
                <h1><?= htmlspecialchars($item['title']) ?></h1>
                <time class="news-date" datetime="<?= htmlspecialchars($item['published_at']) ?>">
                    <?= date('d F Y', strtotime($item['published_at'])) ?>
                </time>
                <?php if (!empty($item['tags'])): ?>
                <div class="tag-list" style="margin-top:.65rem;display:flex;flex-wrap:wrap;gap:.4rem">
                    <?php foreach ($item['tags'] as $tag): ?>
                    <a href="?page=news&tag=<?= urlencode($tag['slug']) ?>" class="tag-chip">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($tag['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </header>

            <div class="news-item-content">
                <?= nl2br(htmlspecialchars($item['content'])) ?>
            </div>
        </article>

        <!-- Comments -->
        <div class="comments-section">
            <h2><i class="fas fa-comments"></i> Reacties
                <?php if (!empty($comments)): ?>
                    <span class="comment-count">(<?= count($comments) ?>)</span>
                <?php endif; ?>
            </h2>

            <?php if (!empty($_SESSION['comment_success'])): ?>
                <div class="flash success"><?= htmlspecialchars($_SESSION['comment_success']) ?></div>
                <?php unset($_SESSION['comment_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['comment_error'])): ?>
                <div class="flash error"><?= htmlspecialchars($_SESSION['comment_error']) ?></div>
                <?php unset($_SESSION['comment_error']); ?>
            <?php endif; ?>

            <?php if ($commentsEnabled): ?>

                <?php if (isset($_SESSION['auth_user'])): ?>
                <form method="POST" action="?page=news-item&id=<?= (int)$item['id'] ?>&action=comment" class="comment-form">
                    <?= \Auth::csrfField() ?>
                    <div class="form-group">
                        <label for="body">Plaats een reactie</label>
                        <textarea id="body" name="body" rows="4" required minlength="2" maxlength="2000"
                                  placeholder="Deel je gedachten…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Plaatsen
                    </button>
                </form>
                <hr class="section-divider" style="margin:1.5rem 0;border:none;border-top:1px solid var(--border-color,#e5e7eb)">
                <?php else: ?>
                <div class="login-prompt" style="margin-bottom:1.5rem;padding:1rem;background:rgba(59,130,246,.06);border-radius:8px;border:1px solid rgba(59,130,246,.2)">
                    <i class="fas fa-lock"></i>
                    <a href="?page=login&redirect=<?= urlencode('?page=news-item&id=' . (int)$item['id']) ?>">Log in</a>
                    of <a href="?page=register">registreer</a> om een reactie te plaatsen.
                </div>
                <?php endif; ?>

                <?php if (empty($comments)): ?>
                    <p style="color:var(--text-muted,#6b7280);text-align:center;padding:2rem 0">
                        <i class="far fa-comment-dots"></i> Nog geen reacties. Wees de eerste!
                    </p>
                <?php else: ?>
                <div class="comment-list">
                    <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-author">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($c['username'] ?? 'User') ?>
                            </span>
                            <span class="comment-date"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
                        </div>
                        <div class="comment-body"><?= nl2br(htmlspecialchars($c['body'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
            <div style="padding:1rem;background:rgba(59,130,246,.06);border-radius:8px;font-size:.9rem;color:var(--text-muted,#6b7280)">
                <i class="fas fa-info-circle"></i> Reacties zijn momenteel uitgeschakeld door de beheerder.
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.container--narrow { max-width: 760px; margin: 0 auto; padding: 0 1rem; }
.news-item-page { padding: 4rem 0; }
.back-link { display:inline-block;margin-bottom:2rem;color:var(--primary-color,#3b82f6);text-decoration:none; }
.back-link:hover { text-decoration:underline; }
.news-item-header { margin-bottom:2rem; }
.news-item-image { width:100%;max-height:400px;object-fit:cover;border-radius:8px;margin-bottom:1.5rem; }
.news-item-header h1 { margin-bottom:.4rem; }
.news-date { font-size:.85rem;color:var(--text-muted,#6b7280); }
.news-item-content { line-height:1.8;font-size:1.05rem; }
.tag-chip { display:inline-block;background:rgba(59,130,246,.12);color:var(--primary-color,#3b82f6);border-radius:4px;padding:.2rem .5rem;font-size:.78rem;font-weight:500;text-decoration:none;transition:background .15s; }
.tag-chip:hover { background:rgba(59,130,246,.22); }
.comments-section { margin-top:3rem; }
.comments-section h2 { font-size:1.2rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem; }
.comment-count { font-size:.85rem;color:var(--text-muted,#6b7280);font-weight:400; }
.comment-form { margin-bottom:1.5rem; }
.comment-form textarea { width:100%;padding:.65rem .9rem;border:1px solid var(--border-color,#e5e7eb);border-radius:6px;font-family:inherit;font-size:.95rem;resize:vertical;background:transparent; }
.comment-form label { display:block;margin-bottom:.4rem;font-size:.85rem;font-weight:500; }
.comment-list { display:flex;flex-direction:column;gap:1rem; }
.comment-item { padding:1rem;background:rgba(255,255,255,.03);border:1px solid var(--border-color,#e5e7eb);border-radius:8px; }
.comment-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem; }
.comment-author { font-weight:600;font-size:.9rem;display:flex;align-items:center;gap:.35rem; }
.comment-date { font-size:.78rem;color:var(--text-muted,#6b7280); }
.comment-body { font-size:.95rem;line-height:1.6; }
</style>
