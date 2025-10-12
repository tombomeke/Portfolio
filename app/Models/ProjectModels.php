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
                'title' => 'Minecraft Economy Plugin',
                'description' => [
                    'nl' => 'Een complete economie plugin voor Minecraft servers met shops, banking en trading systeem. Ondersteunt meerdere valuta\'s en een dynamische markt.',
                    'en' => 'A complete economy plugin for Minecraft servers with shops, banking and trading system. Supports multiple currencies and a dynamic market.'
                ],
                'long_description' => [
                    'nl' => 'Dit project is een volledig uitgewerkt economie systeem voor Minecraft servers. Het bevat een geavanceerde shop system, banking functionaliteit, en een dynamische marktplaats waar spelers items kunnen verhandelen. De plugin is volledig configureerbaar en ondersteunt meerdere valuta\'s.',
                    'en' => 'This project is a fully developed economy system for Minecraft servers. It includes an advanced shop system, banking functionality, and a dynamic marketplace where players can trade items. The plugin is fully configurable and supports multiple currencies.'
                ],
                'tech' => ['Java', 'Spigot API', 'MySQL', 'Maven'],
                'repo_url' => 'https://github.com/tombomeke/mc-economy',
                'demo_url' => null,
                'image' => 'public/images/projects/p1.png',
                'category' => 'minecraft',
                'status' => 'active',
                'features' => [
                    'nl' => [
                        'Multi-valuta ondersteuning',
                        'Dynamisch marktplaats systeem',
                        'Banking met rentetarieven',
                        'Speler-naar-speler handel',
                        'Admin GUI voor beheer',
                        'MySQL database integratie',
                        'Volledig configureerbaar via YAML'
                    ],
                    'en' => [
                        'Multi-currency support',
                        'Dynamic marketplace system',
                        'Banking with interest rates',
                        'Player-to-player trading',
                        'Admin GUI for management',
                        'MySQL database integration',
                        'Fully configurable via YAML'
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
            [
                'id' => 3,
                'title' => 'Task Manager API',
                'description' => [
                    'nl' => 'RESTful API voor taakbeheer met JWT authenticatie en real-time updates via WebSockets. Ondersteunt team collaboration.',
                    'en' => 'RESTful API for task management with JWT authentication and real-time updates via WebSockets. Supports team collaboration.'
                ],
                'long_description' => [
                    'nl' => 'Een professionele RESTful API voor taakbeheer gebouwd met PHP. Bevat JWT authenticatie, real-time updates via WebSockets, en uitgebreide functionaliteit voor team samenwerking inclusief rollen, permissions en activity logs.',
                    'en' => 'A professional RESTful API for task management built with PHP. Includes JWT authentication, real-time updates via WebSockets, and extensive functionality for team collaboration including roles, permissions and activity logs.'
                ],
                'tech' => ['PHP', 'JWT', 'WebSockets', 'PostgreSQL'],
                'repo_url' => 'https://github.com/tombomeke/task-api',
                'demo_url' => null,
                'image' => 'public/images/projects/p3.png',
                'category' => 'api',
                'status' => 'completed',
                'features' => [
                    'nl' => [
                        'JWT authenticatie',
                        'Real-time updates via WebSockets',
                        'Team collaboration features',
                        'Rol-gebaseerde rechten',
                        'Activity logging',
                        'RESTful endpoints',
                        'API documentatie met Swagger'
                    ],
                    'en' => [
                        'JWT authentication',
                        'Real-time updates via WebSockets',
                        'Team collaboration features',
                        'Role-based permissions',
                        'Activity logging',
                        'RESTful endpoints',
                        'API documentation with Swagger'
                    ]
                ]
            ],
            [
                'id' => 4,
                'title' => 'Minecraft Minigames',
                'description' => [
                    'nl' => 'Collection van populaire minigames zoals BedWars, SkyWars en Murder Mystery. Volledig configureerbaar via YAML files.',
                    'en' => 'Collection of popular minigames like BedWars, SkyWars and Murder Mystery. Fully configurable via YAML files.'
                ],
                'long_description' => [
                    'nl' => 'Een uitgebreide collectie van populaire Minecraft minigames. Elk spel is volledig configureerbaar, ondersteunt meerdere arena\'s tegelijk, en bevat een geavanceerd statistics systeem. Perfect voor servers die verschillende game modes willen aanbieden.',
                    'en' => 'An extensive collection of popular Minecraft minigames. Each game is fully configurable, supports multiple arenas simultaneously, and includes an advanced statistics system. Perfect for servers that want to offer different game modes.'
                ],
                'tech' => ['Java', 'Bukkit API', 'Redis', 'MongoDB'],
                'repo_url' => 'https://github.com/tombomeke/minigames',
                'demo_url' => null,
                'image' => 'public/images/projects/p4.png',
                'category' => 'minecraft',
                'status' => 'development',
                'features' => [
                    'nl' => [
                        'BedWars met team systeem',
                        'SkyWars met kits',
                        'Murder Mystery mode',
                        'Multi-arena ondersteuning',
                        'Statistieken tracking',
                        'Leaderboards',
                        'Cosmetische beloningen systeem',
                        'Party systeem'
                    ],
                    'en' => [
                        'BedWars with team system',
                        'SkyWars with kits',
                        'Murder Mystery mode',
                        'Multi-arena support',
                        'Statistics tracking',
                        'Leaderboards',
                        'Cosmetic rewards system',
                        'Party system'
                    ]
                ]
            ],
            [
                'id' => 5,
                'title' => 'E-commerce Dashboard',
                'description' => [
                    'nl' => 'Admin dashboard voor e-commerce platform met analytics, inventory management en order processing.',
                    'en' => 'Admin dashboard for e-commerce platform with analytics, inventory management and order processing.'
                ],
                'long_description' => [
                    'nl' => 'Een professionele admin dashboard voor e-commerce platforms. Bevat real-time analytics met grafieken, geavanceerd inventory management, order processing workflow, en customer relationship management functionaliteit.',
                    'en' => 'A professional admin dashboard for e-commerce platforms. Features real-time analytics with charts, advanced inventory management, order processing workflow, and customer relationship management functionality.'
                ],
                'tech' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Chart.js'],
                'repo_url' => 'https://github.com/tombomeke/ecommerce-dash',
                'demo_url' => 'https://demo.tomdekoning.nl',
                'image' => 'public/images/projects/p5.png',
                'category' => 'web',
                'status' => 'active',
                'features' => [
                    'nl' => [
                        'Real-time analytics dashboard',
                        'Inventory management',
                        'Order processing workflow',
                        'Customer management',
                        'Sales rapporten met grafieken',
                        'Productcatalogus beheer',
                        'Responsive design',
                        'Exporteer data naar CSV/PDF'
                    ],
                    'en' => [
                        'Real-time analytics dashboard',
                        'Inventory management',
                        'Order processing workflow',
                        'Customer management',
                        'Sales reports with charts',
                        'Product catalog management',
                        'Responsive design',
                        'Export data to CSV/PDF'
                    ]
                ]
            ]
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