<?php
/*
================================================================================
BESTAND 6: /app/Views/contact.php
================================================================================
*/
?>
<section class="contact">
    <div class="container">
        <h1><i class="fas fa-envelope"></i> Contact</h1>
        <p class="section-intro">Heb je een vraag of wil je samenwerken? Stuur me een bericht!</p>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="contact-content">
            <div class="contact-form-wrapper">
                <h2>Stuur me een bericht</h2>
                <form method="POST" action="?page=contact" class="contact-form">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Naam *</label>
                        <input type="text" id="name" name="name" required placeholder="Jouw naam" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> E-mail *</label>
                        <input type="email" id="email" name="email" required placeholder="jouw@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="message"><i class="fas fa-comment"></i> Bericht *</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Jouw bericht..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-large"><i class="fas fa-paper-plane"></i> Verzenden</button>
                </form>
            </div>
            <div class="contact-info-wrapper">
                <h2>Direct contact</h2>
                <p>Je kunt me ook direct bereiken via onderstaande kanalen:</p>
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-method-content">
                            <h3>E-mail</h3>
                            <a href="mailto:tom1dekoning@gmail.com">tom1dekoning@gmail.com</a>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fab fa-linkedin"></i></div>
                        <div class="contact-method-content">
                            <h3>LinkedIn</h3>
                            <a href="https://www.linkedin.com/in/tom-dekoning-567523352/" target="_blank">Bekijk profiel</a>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fab fa-github"></i></div>
                        <div class="contact-method-content">
                            <h3>GitHub</h3>
                            <a href="https://github.com/tombomeke" target="_blank">Bekijk repositories</a>
                        </div>
                    </div>
                </div>
                <div class="cv-download-section">
                    <h3>Bekijk mijn CV</h3>
                    <p>Download mijn volledige CV voor meer informatie.</p>
                    <a href="?page=download-cv" class="btn btn-secondary btn-large"><i class="fas fa-download"></i> Download CV (PDF)</a>
                </div>
                <div class="availability-info">
                    <h3>Beschikbaarheid</h3>
                    <p><i class="fas fa-check-circle"></i> Beschikbaar voor freelance projecten</p>
                    <p><i class="fas fa-check-circle"></i> Open voor samenwerkingen</p>
                    <p><i class="fas fa-clock"></i> Reactietijd: binnen 24 uur</p>
                </div>
            </div>
        </div>
    </div>
</section>