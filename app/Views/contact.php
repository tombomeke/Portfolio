<?php
/*
================================================================================
BESTAND: /app/Views/contact.php
================================================================================
*/

require_once __DIR__ . '/../Config/env.php';

$contactEmail = portfolioEnv('PORTFOLIO_CONTACT_EMAIL', 'tom1dekoning@gmail.com');
?>
<section class="contact">
    <div class="container">
        <?php // TODO(i18n): done - hardcoded E-mail/LinkedIn/GitHub headings and PDF suffix replaced with trans() keys. ?>
        <h1><i class="fas fa-envelope"></i> <span data-translate="contact_title"><?= trans('contact_title') ?></span></h1>
        <p class="section-intro" data-translate="contact_intro"><?= trans('contact_intro') ?></p>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span data-translate="contact_success"><?= trans('contact_success') ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        <div class="contact-content">
            <div class="contact-form-wrapper">
                <h2 data-translate="contact_send_message"><?= trans('contact_send_message') ?></h2>
                <form method="POST" action="index.php?page=contact" class="contact-form">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> <span data-translate="contact_name"><?= trans('contact_name') ?></span> *
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               required
                               placeholder="<?= trans('contact_name_placeholder') ?>"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> <span data-translate="contact_email"><?= trans('contact_email') ?></span> *
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               required
                               placeholder="<?= trans('contact_email_placeholder') ?>"
                               data-email-invalid="<?= htmlspecialchars(trans('form_email_invalid'), ENT_QUOTES, 'UTF-8') ?>"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="message">
                            <i class="fas fa-comment"></i> <span data-translate="contact_message"><?= trans('contact_message') ?></span> *
                        </label>
                        <textarea id="message"
                                  name="message"
                                  rows="6"
                                  required
                                  placeholder="<?= trans('contact_message_placeholder') ?>"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-paper-plane"></i> <span data-translate="contact_send"><?= trans('contact_send') ?></span>
                    </button>
                </form>
            </div>
            <div class="contact-info-wrapper">
                <h2 data-translate="contact_direct"><?= trans('contact_direct') ?></h2>
                <p data-translate="contact_methods_intro"><?= trans('contact_methods_intro') ?></p>
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-method-content">
                            <h3><?= trans('contact_email_heading') ?></h3>
                            <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fab fa-linkedin"></i></div>
                        <div class="contact-method-content">
                            <h3><?= trans('contact_linkedin_heading') ?></h3>
                            <a href="https://www.linkedin.com/in/tom-dekoning-567523352/" target="_blank">
                                <span data-translate="contact_view_profile"><?= trans('contact_view_profile') ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="contact-method">
                        <div class="contact-method-icon"><i class="fab fa-github"></i></div>
                        <div class="contact-method-content">
                            <h3><?= trans('contact_github_heading') ?></h3>
                            <a href="https://github.com/tombomeke" target="_blank">
                                <span data-translate="contact_view_repositories"><?= trans('contact_view_repositories') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="cv-download-section">
                    <h3 data-translate="contact_view_cv"><?= trans('contact_view_cv') ?></h3>
                    <p data-translate="contact_cv_description"><?= trans('contact_cv_description') ?></p>
                    <a href="?page=download-cv" class="btn btn-secondary btn-large">
                        <i class="fas fa-download"></i> <span data-translate="hero_download_cv"><?= trans('hero_download_cv') ?></span> <?= trans('contact_cv_pdf_suffix') ?>
                    </a>
                </div>
                <div class="availability-info">
                    <h3 data-translate="contact_availability"><?= trans('contact_availability') ?></h3>
                    <p><i class="fas fa-check-circle"></i> <span data-translate="contact_available_freelance"><?= trans('contact_available_freelance') ?></span></p>
                    <p><i class="fas fa-check-circle"></i> <span data-translate="contact_available_collab"><?= trans('contact_available_collab') ?></span></p>
                    <p><i class="fas fa-clock"></i> <span data-translate="contact_response_time"><?= trans('contact_response_time') ?></span></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    document.querySelectorAll('input[type="email"][data-email-invalid]').forEach(function (input) {
        var message = input.dataset.emailInvalid || '';
        input.addEventListener('invalid', function () {
            this.setCustomValidity(message);
        });
        input.addEventListener('input', function () {
            this.setCustomValidity('');
        });
    });
})();
</script>