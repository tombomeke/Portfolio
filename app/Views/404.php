<?php
/*
================================================================================
BESTAND 7: /app/Views/404.php
================================================================================
*/
?>
<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-animation"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="error-code">404</div>
            <h1>Pagina niet gevonden</h1>
            <p class="error-message">Sorry, de pagina die je zoekt bestaat niet of is verplaatst.</p>
            <div class="error-actions">
                <a href="?page=home" class="btn btn-primary"><i class="fas fa-home"></i> Terug naar Home</a>
                <a href="?page=projects" class="btn btn-secondary"><i class="fas fa-folder"></i> Bekijk Projecten</a>
            </div>
            <div class="error-suggestions">
                <h3>Misschien zoek je:</h3>
                <ul>
                    <li><a href="?page=dev-life"><i class="fas fa-code"></i> Developer Life</a></li>
                    <li><a href="?page=games"><i class="fas fa-gamepad"></i> Gaming Stats</a></li>
                    <li><a href="?page=contact"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php