<?php
/*
================================================================================
BESTAND: /app/Views/dev-life.php (UPDATED with Modal Support)
================================================================================
*/
?>
<?php // TODO(dev-life): Deduplicate repeated Dev Life entries and trim misleading level labels. ?>
<?php // TODO(dev-life): Fill or remove the blank Education & Certificates and Current Learning Goals sections. ?>
<section class="dev-life">
    <div class="container">
        <h1 data-translate="skills_title"><?= trans('skills_title') ?></h1>

        <div class="skills-section">
            <h2><i class="fas fa-code"></i> <span data-translate="skills_title"><?= trans('skills_title') ?></span></h2>
            <p class="section-hint"><i class="fas fa-hand-pointer"></i> <span data-translate="skills_click_details"><?= trans('skills_click_details') ?></span></p>

            <div class="skills-grid">
                <?php foreach ($skills as $skill): ?>
                    <div class="skill-card" data-modal='<?= $skillModel->getModalData($skill) ?>'>
                        <div class="skill-header">
                            <h3><?= htmlspecialchars($skill['name']) ?></h3>
                            <span class="skill-level level-<?= $skill['level'] ?>">
                                <?= htmlspecialchars($skillModel->getLevelText($skill['level'])) ?>
                            </span>
                        </div>
                        <div class="skill-progress">
                            <div class="progress-bar" data-width="<?= $skillModel->getLevelPercentage($skill['level']) ?>%"></div>
                        </div>
                        <p class="skill-notes"><?= htmlspecialchars($skill['notes']) ?></p>
                        <span class="skill-category"><?= htmlspecialchars(trans('category_' . $skill['category'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="education-section">
            <h2><i class="fas fa-graduation-cap"></i> <span data-translate="education_title"><?= trans('education_title') ?></span></h2>
            <p class="section-hint"><i class="fas fa-hand-pointer"></i> <span data-translate="education_click_details"><?= trans('education_click_details') ?></span></p>

            <ul class="education-list">
                <?php foreach ($education as $index => $item): ?>
                    <?php
                        $educationTitle = is_array($item)
                            ? ($item['title'] ?? (isset($item['title_key']) ? trans($item['title_key']) : ''))
                            : $item;
                    ?>
                    <li data-modal='<?= $skillModel->buildEducationModalData($item, $index) ?>'>
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($educationTitle) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="learning-section">
            <h2><i class="fas fa-target"></i> <span data-translate="learning_title"><?= trans('learning_title') ?></span></h2>
            <p class="section-hint"><i class="fas fa-hand-pointer"></i> <span data-translate="learning_click_details"><?= trans('learning_click_details') ?></span></p>

            <ul class="learning-list">
                <?php foreach ($learning_goals as $index => $goal): ?>
                    <?php
                        $learningTitle = is_array($goal)
                            ? ($goal['title'] ?? (isset($goal['title_key']) ? trans($goal['title_key']) : ''))
                            : $goal;
                    ?>
                    <li data-modal='<?= $skillModel->buildLearningModalData($goal, $index) ?>'>
                        <i class="fas fa-arrow-right"></i>
                        <span><?= htmlspecialchars($learningTitle) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<style>
    /* Section hints for clickable elements */
    .section-hint {
        text-align: center;
        color: var(--text-muted);
        font-size: 0.9rem;
        margin-bottom: var(--spacing-lg);
        font-style: italic;
    }

    .section-hint i {
        color: var(--accent-color);
        margin-right: var(--spacing-xs);
    }

    /* Cursor pointer for clickable items */
    .skill-card,
    .education-list li,
    .learning-list li {
        cursor: pointer;
        position: relative;
    }

    /* Hover effect hint */
    .skill-card::after,
    .education-list li::after,
    .learning-list li::after {
        content: '\f06e';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: var(--spacing-md);
        right: var(--spacing-md);
        color: var(--primary-color);
        opacity: 0;
        transition: opacity 0.3s ease;
        font-size: 1.2rem;
    }

    .skill-card:hover::after,
    .education-list li:hover::after,
    .learning-list li:hover::after {
        opacity: 0.7;
    }
</style>
