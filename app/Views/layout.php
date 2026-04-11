<?php
/*
================================================================================
BESTAND: /app/Views/layout.php (UPDATED)
================================================================================
*/

// Include translations
require_once __DIR__ . '/../Config/translations.php';
?>
    <!DOCTYPE html>
    <html lang="<?= Translations::getCurrentLang() ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Portfolio van <?= htmlspecialchars($name ?? 'Tom Dekoning') ?> - Full-stack Developer">
        <title><?= htmlspecialchars($title ?? 'Portfolio') ?> - Tom Dekoning</title>

        <!-- Stylesheets -->
        <link rel="stylesheet" href="public/css/style.css">
        <link rel="stylesheet" href="public/css/modal.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="?page=home">Portfolio</a>
                </div>
                <div class="nav-menu">
                    <a href="?page=home" class="nav-link" data-translate="nav_about"><?= trans('nav_about') ?></a>
                    <a href="?page=dev-life" class="nav-link" data-translate="nav_devlife"><?= trans('nav_devlife') ?></a>
                    <a href="?page=games" class="nav-link" data-translate="nav_games"><?= trans('nav_games') ?></a>
                    <a href="?page=projects" class="nav-link" data-translate="nav_projects"><?= trans('nav_projects') ?></a>
                    <a href="?page=news" class="nav-link">News</a>
                    <a href="?page=faq" class="nav-link">FAQ</a>
                    <a href="?page=readmesync" class="nav-link">ReadmeSync</a>
                    <a href="?page=contact" class="nav-link" data-translate="nav_contact"><?= trans('nav_contact') ?></a>

                    <?php if (isset($_SESSION['auth_user'])): ?>
                    <a href="?page=admin" class="nav-link" style="opacity:.7;font-size:.85rem">
                        <i class="fas fa-gauge-high"></i> Admin
                    </a>
                    <?php endif; ?>

                    <!-- Language Toggle Button -->
                    <button id="lang-toggle" class="lang-toggle" aria-label="Switch language">
                        <span class="flag"><?= Translations::getCurrentLang() === 'nl' ? '🇳🇱' : '🇬🇧' ?></span>
                        <?= Translations::getCurrentLang() === 'nl' ? 'NL' : 'EN' ?>
                    </button>
                </div>
                <div class="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> Tom Dekoning. <span data-translate="footer_rights"><?= trans('footer_rights') ?></span></p>
            <div class="social-links">
                <a href="https://github.com/tombomeke" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                <a href="https://www.linkedin.com/in/tom-dekoning-567523352/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                <a href="mailto:tom1dekoning@gmail.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
            </div>
        </div>
    </footer>

    <!-- Embed translations for JavaScript -->
    <script>
        window.portfolioTranslations = <?= Translations::getJSONTranslations() ?>;
    </script>

    <!-- JavaScript -->
    <script src="public/js/language.js"></script>
    <script src="public/js/modal.js"></script>
    <script src="public/js/script.js"></script>
    </body>
    </html>
<?php