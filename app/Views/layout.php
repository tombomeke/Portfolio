<?php
/*
================================================================================
BESTAND: /app/Views/layout.php (UPDATED)
================================================================================
*/

// Include translations
require_once __DIR__ . '/../Config/env.php';
require_once __DIR__ . '/../Config/translations.php';
require_once __DIR__ . '/../Models/UserModel.php';
$contactEmail = portfolioEnv('PORTFOLIO_CONTACT_EMAIL', 'tom1dekoning@gmail.com');
$styleVersion = (string) (@filemtime(__DIR__ . '/../../public/css/style.css') ?: time());

// TODO(responsive): [P2][done] Prevent long usernames from breaking navbar layout on standard screens.
?>
    <!DOCTYPE html>
    <html lang="<?= Translations::getCurrentLang() ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
           <!-- TODO(seo): [P3] add Open Graph meta tags (og:title, og:description, og:image, og:url)
             for better social sharing previews on LinkedIn/Discord/Twitter. -->
           <!-- TODO(seo): [P3] add a favicon (<link rel="icon">) — currently none is set. -->
        <meta name="description" content="Portfolio van <?= htmlspecialchars($name ?? 'Tom Dekoning') ?> - Full-stack Developer">
        <title><?= htmlspecialchars($title ?? 'Portfolio') ?> - Tom Dekoning</title>

        <!-- Stylesheets -->
        <!-- TODO(security): [P2] add Subresource Integrity and crossorigin attrs for external CDN assets. -->
        <link rel="stylesheet" href="public/css/style.css?v=<?= htmlspecialchars($styleVersion, ENT_QUOTES, 'UTF-8') ?>">
        <link rel="stylesheet" href="public/css/modal.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="?page=home" aria-label="<?= htmlspecialchars(trans('nav_home')) ?>">
                        <i class="fas fa-house"></i> <?= trans('nav_home') ?>
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="?page=home" class="nav-link" data-translate="nav_about"><?= trans('nav_about') ?></a>
                    <a href="?page=dev-life" class="nav-link" data-translate="nav_devlife"><?= trans('nav_devlife') ?></a>
                    <?php // TODO(nav): done - Games removed from nav while section is WIP; route still works for direct access. ?>
                    <a href="?page=projects" class="nav-link" data-translate="nav_projects"><?= trans('nav_projects') ?></a>
                    <!-- TODO(i18n): [P3] 'News', 'FAQ', 'ReadmeSync' are hardcoded — add trans() keys -->
                    <a href="?page=news" class="nav-link"><?= trans('nav_news') ?></a>
                    <a href="?page=faq" class="nav-link"><?= trans('nav_faq') ?></a>
                    <a href="?page=readmesync" class="nav-link"><?= trans('nav_readmesync') ?></a>
                    <a href="?page=contact" class="nav-link" data-translate="nav_contact"><?= trans('nav_contact') ?></a>

                    <?php if (isset($_SESSION['auth_user'])): ?>
                    <?php
                    \Auth::refreshSession();
                    $authUser = $_SESSION['auth_user'];
                    $authRole = $authUser['role'] ?? 'user';

                    // TODO(arch): [P2] this DB query runs inside the view on every page load for logged-in users.
                    // Move avatar refresh logic to a middleware step or controller base method instead.
                    // Session can lag behind profile updates from older login sessions.
                    if (empty($authUser['profile_photo_path']) && !empty($authUser['id'])) {
                        try {
                            $userModel = new UserModel();
                            $freshUser = $userModel->getById((int) $authUser['id']);
                            if (!empty($freshUser['profile_photo_path'])) {
                                $authUser['profile_photo_path'] = $freshUser['profile_photo_path'];
                                $_SESSION['auth_user']['profile_photo_path'] = $freshUser['profile_photo_path'];
                            }
                        } catch (\Throwable $e) {
                            // Ignore DB lookup failures and keep initials fallback.
                        }
                    }

                    $rawAvatarPath = trim((string) ($authUser['profile_photo_path'] ?? ''));
                    $avatarPath = '';
                    if ($rawAvatarPath !== '') {
                        $avatarPath = preg_match('#^public/images/uploads/#', $rawAvatarPath)
                            ? $rawAvatarPath
                            : 'public/images/uploads/avatars/' . ltrim($rawAvatarPath, '/');
                    }

                    $fullNavUsername = (string) ($authUser['username'] ?? '');
                    $displayNavUsername = strlen($fullNavUsername) > 18
                        ? substr($fullNavUsername, 0, 18) . '...'
                        : $fullNavUsername;
                    ?>
                    <div class="nav-user-dropdown">
                        <button class="nav-user-trigger" aria-expanded="false" aria-haspopup="true" title="<?= htmlspecialchars($fullNavUsername) ?>">
                            <span class="nav-user-avatar">
                                <?php if ($avatarPath): ?>
                                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="<?= htmlspecialchars($authUser['username']) ?>" class="nav-user-avatar-image">
                                <?php else: ?>
                                    <?= htmlspecialchars(strtoupper(substr($authUser['username'], 0, 1))) ?>
                                <?php endif; ?>
                            </span>
                            <span class="nav-user-name"><?= htmlspecialchars($displayNavUsername) ?></span>
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
                                <i class="fas fa-gauge-high"></i> <?= trans('nav_dashboard') ?>
                            </a>
                            <?php endif; ?>
                            <a href="?page=profile&u=<?= urlencode($authUser['username']) ?>" class="nav-user-item">
                                <i class="fas fa-user-circle"></i> <?= trans('nav_my_profile') ?>
                            </a>
                            <a href="?page=settings" class="nav-user-item">
                                <i class="fas fa-sliders-h"></i> <?= trans('nav_settings') ?>
                            </a>
                            <div class="nav-user-divider"></div>
                            <a href="?page=logout" class="nav-user-item nav-user-item--danger">
                                <i class="fas fa-right-from-bracket"></i> <?= trans('nav_logout') ?>
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="?page=login" class="nav-link nav-link--login"><i class="fas fa-sign-in-alt"></i> <?= trans('nav_login') ?></a>
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
                <?php // TODO(ui): done - footer mail button guarded; hides when PORTFOLIO_CONTACT_EMAIL env is empty. ?>
                <?php if (!empty($contactEmail)): ?>
                <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
                <?php endif; ?>
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