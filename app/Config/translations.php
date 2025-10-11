<?php
// /app/Config/translations.php
/**
 * ============================================
 * TRANSLATIONS SYSTEM
 * ============================================
 * Easy multi-language support for your portfolio
 *
 * HOW TO USE:
 * 1. In PHP: echo trans('welcome_message');
 * 2. In JS: translate('welcome_message')
 * 3. Add new translations below in both NL and EN arrays
 *
 * HOW TO ADD NEW LANGUAGES:
 * 1. Copy the 'nl' or 'en' array
 * 2. Rename it (e.g., 'fr', 'de', 'es')
 * 3. Translate all values
 * 4. Add language option in layout.php language toggle
 */

class Translations {
    private static $currentLang = 'nl';

    private static $translations = [
        'nl' => [
            // Navigation
            'nav_about' => 'About',
            'nav_devlife' => 'Dev Life',
            'nav_games' => 'Games',
            'nav_projects' => 'Projecten',
            'nav_contact' => 'Contact',

            // Hero Section
            'hero_greeting' => 'Hoi, ik ben',
            'hero_intro' => 'Full-stack developer met passie voor gaming en open source projecten. Gespecialiseerd in PHP, JavaScript en Minecraft plugin development.',
            'hero_view_work' => 'Bekijk mijn werk',
            'hero_download_cv' => 'Download CV',

            // Skills Section
            'skills_title' => 'Skills & Technologieën',
            'skills_level_beginner' => 'Leer',
            'skills_level_intermediate' => 'Basis',
            'skills_level_advanced' => 'Goed',
            'skills_click_details' => 'Klik voor meer details',

            // Skills Modal
            'modal_skill_level' => 'Niveau',
            'modal_skill_category' => 'Categorie',
            'modal_skill_experience' => 'Ervaring',
            'modal_skill_projects' => 'Gebruikte Projecten',
            'modal_close' => 'Sluiten',

            // Projects Section
            'projects_title' => 'Mijn Projecten',
            'projects_intro' => 'Een overzicht van mijn recente projecten en bijdragen',
            'projects_filter_all' => 'Alle',
            'projects_filter_minecraft' => 'Minecraft',
            'projects_filter_web' => 'Web',
            'projects_filter_api' => 'API',
            'projects_view_code' => 'Code',
            'projects_view_demo' => 'Demo',
            'projects_click_details' => 'Klik voor meer details',

            // Projects Modal
            'modal_project_tech' => 'Technologieën',
            'modal_project_features' => 'Features',
            'modal_project_github' => 'Bekijk op GitHub',
            'modal_project_demo' => 'Live Demo',
            'modal_project_status' => 'Status',
            'modal_project_status_active' => 'Actief',
            'modal_project_status_completed' => 'Afgerond',
            'modal_project_status_development' => 'In Ontwikkeling',

            // Education Section
            'education_title' => 'Opleiding & Certificaten',
            'education_click_details' => 'Klik voor meer details',

            // Education Modal
            'modal_education_institution' => 'Instelling',
            'modal_education_period' => 'Periode',
            'modal_education_description' => 'Beschrijving',
            'modal_education_skills' => 'Opgedane Skills',

            // Learning Goals Section
            'learning_title' => 'Huidige Leerdoelen',
            'learning_click_details' => 'Klik voor meer details',

            // Learning Modal
            'modal_learning_goal' => 'Leerdoel',
            'modal_learning_progress' => 'Voortgang',
            'modal_learning_resources' => 'Bronnen',
            'modal_learning_timeline' => 'Tijdlijn',

            // Contact Section
            'contact_title' => 'Contact',
            'contact_intro' => 'Heb je een vraag of wil je samenwerken? Stuur me een bericht!',
            'contact_name' => 'Naam',
            'contact_email' => 'E-mail',
            'contact_message' => 'Bericht',
            'contact_send' => 'Verzenden',
            'contact_success' => 'Bericht succesvol verzonden! Ik neem zo snel mogelijk contact met je op.',
            'contact_error' => 'Er is een fout opgetreden bij het verzenden.',
            'contact_direct' => 'Direct contact',
            'contact_methods_intro' => 'Je kunt me ook direct bereiken via onderstaande kanalen:',
            'contact_availability' => 'Beschikbaarheid',
            'contact_available_freelance' => 'Beschikbaar voor freelance projecten',
            'contact_available_collab' => 'Open voor samenwerkingen',
            'contact_response_time' => 'Reactietijd: binnen 24 uur',

            // Form Validation
            'form_required' => 'Dit veld is verplicht',
            'form_email_invalid' => 'Voer een geldig e-mailadres in',
            'form_name_short' => 'Naam moet minimaal 2 karakters bevatten',
            'form_message_short' => 'Bericht moet minimaal 10 karakters bevatten',

            // Footer
            'footer_rights' => 'Alle rechten voorbehouden',

            // 404 Page
            'error_404_title' => 'Pagina niet gevonden',
            'error_404_message' => 'Sorry, de pagina die je zoekt bestaat niet of is verplaatst.',
            'error_404_home' => 'Terug naar Home',
            'error_404_suggestions' => 'Misschien zoek je:',

            // Games Section
            'games_title' => 'Gaming Stats',
            'games_minecraft' => 'Minecraft',
            'games_r6siege' => 'Rainbow Six Siege',
            'games_server_info' => 'Server Info',
            'games_player_stats' => 'Speler Statistieken',
            'games_top_players' => 'Top Spelers',
            'games_last_update' => 'Laatst bijgewerkt',

            // Categories
            'category_languages' => 'Talen',
            'category_frameworks' => 'Frameworks',
            'category_database' => 'Database',
            'category_tools' => 'Tools',

            // General
            'loading' => 'Laden...',
            'read_more' => 'Lees meer',
            'view_all' => 'Bekijk alles',
            'close' => 'Sluiten',
            'open' => 'Openen',
            'download' => 'Download',
            'copied' => 'Gekopieerd!',
        ],

        'en' => [
            // Navigation
            'nav_about' => 'About',
            'nav_devlife' => 'Dev Life',
            'nav_games' => 'Games',
            'nav_projects' => 'Projects',
            'nav_contact' => 'Contact',

            // Hero Section
            'hero_greeting' => 'Hi, I\'m',
            'hero_intro' => 'Full-stack developer with a passion for gaming and open source projects. Specialized in PHP, JavaScript and Minecraft plugin development.',
            'hero_view_work' => 'View my work',
            'hero_download_cv' => 'Download CV',

            // Skills Section
            'skills_title' => 'Skills & Technologies',
            'skills_level_beginner' => 'Learning',
            'skills_level_intermediate' => 'Intermediate',
            'skills_level_advanced' => 'Advanced',
            'skills_click_details' => 'Click for more details',

            // Skills Modal
            'modal_skill_level' => 'Level',
            'modal_skill_category' => 'Category',
            'modal_skill_experience' => 'Experience',
            'modal_skill_projects' => 'Used in Projects',
            'modal_close' => 'Close',

            // Projects Section
            'projects_title' => 'My Projects',
            'projects_intro' => 'An overview of my recent projects and contributions',
            'projects_filter_all' => 'All',
            'projects_filter_minecraft' => 'Minecraft',
            'projects_filter_web' => 'Web',
            'projects_filter_api' => 'API',
            'projects_view_code' => 'Code',
            'projects_view_demo' => 'Demo',
            'projects_click_details' => 'Click for more details',

            // Projects Modal
            'modal_project_tech' => 'Technologies',
            'modal_project_features' => 'Features',
            'modal_project_github' => 'View on GitHub',
            'modal_project_demo' => 'Live Demo',
            'modal_project_status' => 'Status',
            'modal_project_status_active' => 'Active',
            'modal_project_status_completed' => 'Completed',
            'modal_project_status_development' => 'In Development',

            // Education Section
            'education_title' => 'Education & Certificates',
            'education_click_details' => 'Click for more details',

            // Education Modal
            'modal_education_institution' => 'Institution',
            'modal_education_period' => 'Period',
            'modal_education_description' => 'Description',
            'modal_education_skills' => 'Skills Acquired',

            // Learning Goals Section
            'learning_title' => 'Current Learning Goals',
            'learning_click_details' => 'Click for more details',

            // Learning Modal
            'modal_learning_goal' => 'Learning Goal',
            'modal_learning_progress' => 'Progress',
            'modal_learning_resources' => 'Resources',
            'modal_learning_timeline' => 'Timeline',

            // Contact Section
            'contact_title' => 'Contact',
            'contact_intro' => 'Have a question or want to collaborate? Send me a message!',
            'contact_name' => 'Name',
            'contact_email' => 'Email',
            'contact_message' => 'Message',
            'contact_send' => 'Send',
            'contact_success' => 'Message sent successfully! I\'ll get back to you as soon as possible.',
            'contact_error' => 'An error occurred while sending.',
            'contact_direct' => 'Direct Contact',
            'contact_methods_intro' => 'You can also reach me directly through these channels:',
            'contact_availability' => 'Availability',
            'contact_available_freelance' => 'Available for freelance projects',
            'contact_available_collab' => 'Open for collaborations',
            'contact_response_time' => 'Response time: within 24 hours',

            // Form Validation
            'form_required' => 'This field is required',
            'form_email_invalid' => 'Please enter a valid email address',
            'form_name_short' => 'Name must be at least 2 characters',
            'form_message_short' => 'Message must be at least 10 characters',

            // Footer
            'footer_rights' => 'All rights reserved',

            // 404 Page
            'error_404_title' => 'Page not found',
            'error_404_message' => 'Sorry, the page you\'re looking for doesn\'t exist or has been moved.',
            'error_404_home' => 'Back to Home',
            'error_404_suggestions' => 'You might be looking for:',

            // Games Section
            'games_title' => 'Gaming Stats',
            'games_minecraft' => 'Minecraft',
            'games_r6siege' => 'Rainbow Six Siege',
            'games_server_info' => 'Server Info',
            'games_player_stats' => 'Player Statistics',
            'games_top_players' => 'Top Players',
            'games_last_update' => 'Last updated',

            // Categories
            'category_languages' => 'Languages',
            'category_frameworks' => 'Frameworks',
            'category_database' => 'Database',
            'category_tools' => 'Tools',

            // General
            'loading' => 'Loading...',
            'read_more' => 'Read more',
            'view_all' => 'View all',
            'close' => 'Close',
            'open' => 'Open',
            'download' => 'Download',
            'copied' => 'Copied!',
        ]
    ];

    /**
     * Initialize language from cookie or browser preference
     */
    public static function init() {
        // Check cookie first
        if (isset($_COOKIE['portfolio_lang'])) {
            self::$currentLang = $_COOKIE['portfolio_lang'];
        }
        // Fallback to browser language
        else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (in_array($browserLang, ['nl', 'en'])) {
                self::$currentLang = $browserLang;
            }
        }
    }

    /**
     * Get translation for a key
     * @param string $key Translation key
     * @param string|null $lang Optional language override
     * @return string Translated text
     */
    public static function get($key, $lang = null) {
        $lang = $lang ?? self::$currentLang;

        if (isset(self::$translations[$lang][$key])) {
            return self::$translations[$lang][$key];
        }

        // Fallback to Dutch if translation not found
        if (isset(self::$translations['nl'][$key])) {
            return self::$translations['nl'][$key];
        }

        // Return key if nothing found (helps debugging)
        return "[{$key}]";
    }

    /**
     * Get current language
     * @return string Current language code
     */
    public static function getCurrentLang() {
        return self::$currentLang;
    }

    /**
     * Set current language
     * @param string $lang Language code
     */
    public static function setLang($lang) {
        if (isset(self::$translations[$lang])) {
            self::$currentLang = $lang;
            // Set cookie for 1 year
            setcookie('portfolio_lang', $lang, time() + (365 * 24 * 60 * 60), '/');
        }
    }

    /**
     * Get all translations for JavaScript
     * @return string JSON encoded translations
     */
    public static function getJSONTranslations() {
        return json_encode(self::$translations);
    }

    /**
     * Get available languages
     * @return array Array of language codes
     */
    public static function getAvailableLanguages() {
        return array_keys(self::$translations);
    }
}

// Initialize translations
Translations::init();

/**
 * Helper function for easy translation access
 * Usage: trans('welcome_message')
 */
function trans($key, $lang = null) {
    return Translations::get($key, $lang);
}

?>