<?php
/*
================================================================================
BESTAND 1: /app/Views/layout.php
================================================================================
Kopieer alles tussen de lijnen naar layout.php
*/
?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Portfolio van <?= htmlspecialchars($name ?? 'Jouw Naam') ?> - Full-stack Developer">
        <title><?= htmlspecialchars($title ?? 'Portfolio') ?> - Jouw Naam</title>
        <link rel="stylesheet" href="public/css/style.css">
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
                    <a href="?page=home" class="nav-link">About</a>
                    <a href="?page=dev-life" class="nav-link">Dev Life</a>
                    <a href="?page=games" class="nav-link">Games</a>
                    <a href="?page=projects" class="nav-link">Projecten</a>
                    <a href="?page=contact" class="nav-link">Contact</a>
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
            <p>&copy; <?= date('Y') ?> Jouw Naam. Alle rechten voorbehouden.</p>
            <div class="social-links">
                <a href="https://github.com/jouwusername" target="_blank"><i class="fab fa-github"></i></a>
                <a href="https://linkedin.com/in/jouwprofiel" target="_blank"><i class="fab fa-linkedin"></i></a>
                <a href="mailto:jouw@email.com"><i class="fas fa-envelope"></i></a>
            </div>
        </div>
    </footer>
    <script src="public/js/script.js"></script>
    </body>
    </html>
<?php