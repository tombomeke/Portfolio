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
                    <?php
                    $authUser = $_SESSION['auth_user'];
                    $authRole = $authUser['role'] ?? 'user';
                    $rawAvatarPath = trim((string) ($authUser['profile_photo_path'] ?? ''));
                    $avatarPath = '';
                    if ($rawAvatarPath !== '') {
                        $avatarPath = preg_match('#^public/images/uploads/#', $rawAvatarPath)
                            ? $rawAvatarPath
                            : 'public/images/uploads/avatars/' . ltrim($rawAvatarPath, '/');
                    }
                    ?>
                    <div class="nav-user-dropdown">
                        <button class="nav-user-trigger" aria-expanded="false" aria-haspopup="true">
                            <span class="nav-user-avatar">
                                <?php if ($avatarPath): ?>
                                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="<?= htmlspecialchars($authUser['username']) ?>" class="nav-user-avatar-image">
                                <?php else: ?>
                                    <?= htmlspecialchars(strtoupper(substr($authUser['username'], 0, 1))) ?>
                                <?php endif; ?>
                            </span>
                            <span class="nav-user-name"><?= htmlspecialchars($authUser['username']) ?></span>
                            <?php if ($authRole === 'owner'): ?>
                                <span class="nav-user-badge">owner</span>
                            <?php elseif ($authRole === 'admin'): ?>
                                <span class="nav-user-badge">admin</span>
                            <?php endif; ?>
                            <i class="fas fa-chevron-down nav-user-chevron"></i>
                        </button>
                        <div class="nav-user-menu">
                            <?php if (in_array($authRole, ['owner', 'admin'], true)): ?>
                            <a href="?page=admin" class="nav-user-item">
                                <i class="fas fa-gauge-high"></i> Dashboard
                            </a>
                            <?php endif; ?>
                            <a href="?page=profile&u=<?= urlencode($authUser['username']) ?>" class="nav-user-item">
                                <i class="fas fa-user-circle"></i> Mijn profiel
                            </a>
                            <div class="nav-user-divider"></div>
                            <a href="?page=logout" class="nav-user-item nav-user-item--danger">
                                <i class="fas fa-right-from-bracket"></i> Uitloggen
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="?page=login" class="nav-link nav-link--login"><i class="fas fa-sign-in-alt"></i> Inloggen</a>
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