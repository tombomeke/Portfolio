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

        // Update toggle button
        const toggleBtn = document.getElementById('lang-toggle');
        if (toggleBtn) {
            this.updateToggleButton(toggleBtn);
        }

        // Apply new language
        this.applyLanguage(lang);

        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('languageChanged', {
            detail: { language: lang }
        }));

        console.log('Language switched to:', lang);
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