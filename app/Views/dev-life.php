<?php
/*
================================================================================
BESTAND 3: /app/Views/dev-life.php
================================================================================
*/
?>
<section class="dev-life">
    <div class="container">
        <h1>Developer Life</h1>
        <div class="skills-section">
            <h2><i class="fas fa-code"></i> Skills & Technologieën</h2>
            <div class="skills-grid">
                <?php foreach ($skills as $skill): ?>
                    <div class="skill-card">
                        <div class="skill-header">
                            <h3><?= htmlspecialchars($skill['name']) ?></h3>
                            <span class="skill-level level-<?= $skill['level'] ?>">
                                <?= htmlspecialchars($skillModel->getLevelText($skill['level'])) ?>
                            </span>
                        </div>
                        <div class="skill-progress">
                            <div class="progress-bar" style="width: <?= $skillModel->getLevelPercentage($skill['level']) ?>%"></div>
                        </div>
                        <p class="skill-notes"><?= htmlspecialchars($skill['notes']) ?></p>
                        <span class="skill-category"><?= htmlspecialchars($skill['category']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="education-section">
            <h2><i class="fas fa-graduation-cap"></i> Opleiding & Certificaten</h2>
            <ul class="education-list">
                <?php foreach ($education as $item): ?>
                    <li><i class="fas fa-check-circle"></i><span><?= htmlspecialchars($item) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="learning-section">
            <h2><i class="fas fa-target"></i> Huidige Leerdoelen</h2>
            <ul class="learning-list">
                <?php foreach ($learning_goals as $goal): ?>
                    <li><i class="fas fa-arrow-right"></i><span><?= htmlspecialchars($goal) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>