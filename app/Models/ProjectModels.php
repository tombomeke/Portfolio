<?php
// ============================================
// FILE: /app/Models/ProjectModel.php (UPDATED)
// ============================================
/**
 * PROJECT MODEL - Easy Content Management
 *
 * HOW TO ADD A NEW PROJECT:
 * 1. Copy an existing project array below
 * 2. Change the values:
 *    - 'id': Unique number (increment from last)
 *    - 'title': Project name
 *    - 'description': Short description (1-2 sentences)
 *    - 'long_description': (Optional) Detailed description
 *    - 'tech': Array of technologies used
 *    - 'category': 'minecraft', 'web', or 'api'
 *    - 'repo_url': GitHub link (or null if private)
 *    - 'demo_url': Live demo link (or null if not available)
 *    - 'image': Path to screenshot (or null)
 *    - 'status': 'active', 'completed', or 'development'
 *    - 'features': (Optional) Array of key features
 * 3. Save the file - that's it!
 */

class ProjectModel {
    /**
     * Get all projects with modal data
     * @return array All projects
     */
    public function getAllProjects() {
        $lang = Translations::getCurrentLang();

        $projectsData = [
            [
                'id' => 1,
                'title' => 'RPG Manager',
                'description' => [
                    'nl' => 'RPG Manager is een console-based role-playing game framework in C# met een modulair systeem voor werelden, locaties, helden en wapens. Inclusief JSON-saves en een uitbreidbare architectuur.',
                    'en' => 'RPG Manager is a console-based role-playing game framework in C# featuring a modular system for worlds, locations, heroes and weapons. Includes JSON saves and an extensible architecture.'
                ],
                'long_description' => [
                    'nl' => 'Het project bestaat uit namespaces zoals <code>Characters</code>, <code>Weapons</code>, <code>Worlds</code>, <code>Locations</code>, <code>UI</code> en <code>Saves</code>. Spelers maken helden (bv. Warrior, Mage), kiezen wapens en verkennen werelden. Voortgang wordt opgeslagen via <code>System.Text.Json</code>.<br><br><strong>Belangrijkste kenmerken</strong><br>• Modulair systeem (makkelijk uitbreiden met nieuwe werelden/klassen/wapens)<br>• Wapen- en inventarissysteem met rarity, durability en upgrades<br>• Persistente opslag met JSON-saves<br>• Heldensysteem met levels/skills<br>• Wereldstructuur met moeilijkheidsgraden en unlockbare locaties',
                    'en' => 'The project is organized into namespaces like <code>Characters</code>, <code>Weapons</code>, <code>Worlds</code>, <code>Locations</code>, <code>UI</code>, and <code>Saves</code>. Players create heroes (e.g., Warrior, Mage), choose weapons, and explore worlds. Progress is saved using <code>System.Text.Json</code>.<br><br><strong>Key features</strong><br>• Modular system (easily add worlds/classes/weapons)<br>• Weapon & inventory with rarity, durability, and upgrades<br>• Persistent storage via JSON saves<br>• Hero system with levels/skills<br>• World structure with difficulties and unlockable locations'
                ],
                'tech' => ['C#', '.NET', 'Object-Oriented Design', 'System.Text.Json', 'Console Application'],
                'repo_url' => 'https://github.com/tombomeke-ehb/RPGManager',
                'demo_url' => 'https://github.com/tombomeke-ehb/RPGManager/releases/latest/',
                'image' => 'public/images/projects/p1.png',
                'category' => 'cli',
                'status' => 'development',
                'features' => [
                    'nl' => [
                        'Modulaire architectuur',
                        'JSON-opslaan en laden',
                        'Wapenrarity & upgrades',
                        'Heldensysteem met levels',
                        'Uitbreidbare wereld/locatie-structuur'
                    ],
                    'en' => [
                        'Modular architecture',
                        'JSON save/load',
                        'Weapon rarity & upgrades',
                        'Hero system with levels',
                        'Extensible world/location structure'
                    ]
                ]
            ],
            [
                'id' => 2,
                'title' => 'Portfolio Website',
                'description' => [
                    'nl' => 'Deze responsive portfolio website gebouwd met vanilla PHP en moderne CSS. Features include project showcase, skill tracking en contact form.',
                    'en' => 'This responsive portfolio website built with vanilla PHP and modern CSS. Features include project showcase, skill tracking and contact form.'
                ],
                'long_description' => [
                    'nl' => 'Een modern portfolio website gebouwd van scratch met PHP MVC architectuur. Bevat een geavanceerd modal systeem, meertalige ondersteuning (NL/EN), en een responsief design dat perfect werkt op alle apparaten.',
                    'en' => 'A modern portfolio website built from scratch with PHP MVC architecture. Features an advanced modal system, multi-language support (NL/EN), and a responsive design that works perfectly on all devices.'
                ],
                'tech' => ['PHP', 'JavaScript', 'CSS Grid', 'HTML5'],
                'repo_url' => 'https://github.com/tombomeke/portfolio',
                'demo_url' => 'https://tomdekoning.nl',
                'image' => 'public/images/projects/p2.png',
                'category' => 'web',
                'status' => 'active',
                'features' => [
                    'nl' => [
                        'Meertalige ondersteuning (NL/EN)',
                        'Universeel modal systeem',
                        'Responsive design',
                        'Contactformulier met validatie',
                        'Dark theme',
                        'Vloeiende animaties',
                        'SEO geoptimaliseerd'
                    ],
                    'en' => [
                        'Multi-language support (NL/EN)',
                        'Universal modal system',
                        'Responsive design',
                        'Contact form with validation',
                        'Dark theme',
                        'Smooth animations',
                        'SEO optimized'
                    ]
                ]
            ],
        ];


        // Convert to current language
        $projects = [];
        foreach ($projectsData as $project) {
            $project['description'] = $project['description'][$lang];
            $project['long_description'] = $project['long_description'][$lang] ?? $project['description'];
            $project['features'] = $project['features'][$lang] ?? [];
            $projects[] = $project;
        }

        return $projects;
    }

    /**
     * Get projects by category
     * @param string $category Category name
     * @return array Filtered projects
     */
    public function getProjectsByCategory($category) {
        $projects = $this->getAllProjects();
        return array_filter($projects, function($project) use ($category) {
            return $project['category'] === $category;
        });
    }

    /**
     * Get project by ID
     * @param int $id Project ID
     * @return array|null Project data or null if not found
     */
    public function getProjectById($id) {
        $projects = $this->getAllProjects();
        foreach ($projects as $project) {
            if ($project['id'] == $id) {
                return $project;
            }
        }
        return null;
    }

    /**
     * Get modal data for a project (JSON encoded)
     * @param array $project Project array
     * @return string JSON string for data-modal attribute
     */
    public function getModalData($project) {
        return htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8');
    }
}
?>