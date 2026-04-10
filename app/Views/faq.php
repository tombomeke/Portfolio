<section class="faq-page">
    <div class="container container--narrow">
        <h1>FAQ</h1>

        <?php if (empty($categories)): ?>
            <p class="no-content">Nog geen FAQ items beschikbaar.</p>
        <?php endif; ?>

        <?php foreach ($categories as $cat): ?>
        <div class="faq-category">
            <h2><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></h2>

            <?php foreach ($cat['items'] as $item): ?>
            <details class="faq-item">
                <summary><?= htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8') ?></summary>
                <div class="faq-answer">
                    <?= nl2br(htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8')) ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
.faq-page { padding: 4rem 0; }
.faq-category { margin-bottom: 2.5rem; }
.faq-category h2 { margin-bottom: 1rem; font-size: 1.3rem; }
.faq-item { border: 1px solid var(--border-color, #e5e7eb); border-radius: 8px; margin-bottom: .6rem; }
.faq-item summary { padding: 1rem 1.2rem; cursor: pointer; font-weight: 500; list-style: none; display: flex; justify-content: space-between; }
.faq-item summary::after { content: '+'; font-size: 1.2rem; transition: transform .2s; }
.faq-item[open] summary::after { transform: rotate(45deg); }
.faq-answer { padding: .75rem 1.2rem 1.2rem; color: var(--text-muted, #4b5563); line-height: 1.7; }
.no-content { color: var(--text-muted, #6b7280); margin-top: 2rem; }
</style>
