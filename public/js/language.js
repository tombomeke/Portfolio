// /public/js/language.js
/**
 * ============================================
 * LANGUAGE SWITCHER
 * ============================================
 * Handles client-side language switching with localStorage
 * and dynamic content updates without page reload
 */

class LanguageSwitcher {
    constructor() {
        this.currentLang = this.getStoredLanguage() || 'nl';
        this.translations = {};
        this.init();
    }

    /**
     * Initialize language switcher
     */
    init() {
        // Load translations from PHP
        this.loadTranslations();

        // Set up toggle button
        this.setupToggleButton();

        // Apply current language
        this.applyLanguage(this.currentLang);

        console.log('Language Switcher initialized:', this.currentLang);
    }

    /**
     * Load translations from PHP (embedded in page)
     */
    loadTranslations() {
        // Translations are embedded by PHP in layout.php
        if (window.portfolioTranslations) {
            this.translations = window.portfolioTranslations;
        }
    }

    /**
     * Get stored language from localStorage
     */
    getStoredLanguage() {
        return localStorage.getItem('portfolio_lang');
    }

    /**
     * Store language preference
     */
    storeLanguage(lang) {
        localStorage.setItem('portfolio_lang', lang);
        // Also set cookie for PHP
        document.cookie = `portfolio_lang=${lang}; path=/; max-age=${365*24*60*60}`;
    }

    /**
     * Setup language toggle button
     */
    setupToggleButton() {
        const toggleBtn = document.getElementById('lang-toggle');
        if (!toggleBtn) return;

        // Set initial button state
        this.updateToggleButton(toggleBtn);

        // Add click event
        toggleBtn.addEventListener('click', () => {
            const newLang = this.currentLang === 'nl' ? 'en' : 'nl';
            this.switchLanguage(newLang);
        });
    }

    /**
     * Update toggle button appearance
     */
    updateToggleButton(btn) {
        const flag = this.currentLang === 'nl' ? '🇳🇱' : '🇬🇧';
        const text = this.currentLang === 'nl' ? 'NL' : 'EN';
        btn.innerHTML = `<span class="flag">${flag}</span> ${text}`;
        btn.setAttribute('data-lang', this.currentLang);
        btn.setAttribute('title', `Switch to ${this.currentLang === 'nl' ? 'English' : 'Nederlands'}`);
    }

    /**
     * Switch to a different language
     */
    switchLanguage(lang) {
        if (lang === this.currentLang) return;

        this.currentLang = lang;
        this.storeLanguage(lang);

        // Reload page to apply PHP translations
        window.location.reload();
    }

    /**
     * Apply language to all translatable elements
     */
    applyLanguage(lang) {
        // Find all elements with data-translate attribute
        const elements = document.querySelectorAll('[data-translate]');

        elements.forEach(element => {
            const key = element.getAttribute('data-translate');
            const translation = this.translate(key, lang);

            if (translation !== `[${key}]`) {
                // Check if it's an input placeholder
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = translation;
                } else {
                    element.textContent = translation;
                }
            }
        });

        // Update form input placeholders separately
        const nameInput = document.querySelector('input[name="name"]');
        const emailInput = document.querySelector('input[name="email"]');
        const messageInput = document.querySelector('textarea[name="message"]');

        if (nameInput) {
            nameInput.placeholder = this.translate('contact_name_placeholder', lang);
        }
        if (emailInput) {
            emailInput.placeholder = this.translate('contact_email_placeholder', lang);
        }
        if (messageInput) {
            messageInput.placeholder = this.translate('contact_message_placeholder', lang);
        }

        // Update document language attribute
        document.documentElement.lang = lang;
    }

    /**
     * Get translation for a key
     */
    translate(key, lang = null) {
        lang = lang || this.currentLang;

        if (this.translations[lang] && this.translations[lang][key]) {
            return this.translations[lang][key];
        }

        // Fallback to Dutch
        if (this.translations['nl'] && this.translations['nl'][key]) {
            return this.translations['nl'][key];
        }

        return `[${key}]`;
    }

    /**
     * Get current language
     */
    getCurrentLanguage() {
        return this.currentLang;
    }
}

// Initialize when DOM is ready
let languageSwitcher;

function initLanguageSwitcher() {
    languageSwitcher = new LanguageSwitcher();

    // Expose globally for modal system
    window.translate = (key, lang) => languageSwitcher.translate(key, lang);
    window.getCurrentLang = () => languageSwitcher.getCurrentLanguage();
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanguageSwitcher);
} else {
    initLanguageSwitcher();
}