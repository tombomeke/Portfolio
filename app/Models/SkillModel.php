<?php
// ============================================
// FILE: /app/Models/SkillModel.php (UPDATED)
// ============================================
/**
 * SKILL MODEL - Easy Content Management
 *
 * HOW TO ADD A NEW SKILL:
 * 1. Copy an existing skill array below
 * 2. Change the values:
 *    - 'name': Name of the technology (e.g., 'React')
 *    - 'level': 1 = Learning, 2 = Intermediate, 3 = Advanced
 *    - 'category': 'languages', 'frameworks', 'database', or 'tools'
 *    - 'notes': Short description
 *    - 'projects': (Optional) Array of project names using this skill
 * 3. Save the file - that's it!
 *
 * EXAMPLE:
 * [
 *     'name' => 'TypeScript',
 *     'level' => 2,
 *     'category' => 'languages',
 *     'notes' => 'Type-safe JavaScript for large applications',
 *     'projects' => ['E-commerce Dashboard', 'Task Manager API']
 * ]
 *
 * EDUCATION / LEARNING GOALS:
 * - Fill the $education and $learningGoals arrays below (per language).
 * - Leave them empty to hide the sections for now.
 * - Use the same structure for NL/EN so it stays consistent.
 *
 * EDUCATION ITEM TEMPLATE (uncomment & copy):
 * [
 *     'title_key' => 'education_hbo_title',       // of direct 'title' => '...'
 *     'institution_key' => 'education_hbo_inst',  // of 'institution' => '...'
 *     'period_key' => 'education_hbo_period',     // of 'period' => '...'
 *     'description_key' => 'education_hbo_desc',  // of 'description' => '...'
 *     'skills' => [
 *         ['label_key' => 'education_hbo_skill_arch'],
 *         'Team Collaboration' // strings blijven ook werken
 *     ],
 *     'certificate_url' => 'https://example.com/certificate.pdf' // optional
 * ]
 *
 * LEARNING GOAL TEMPLATE:
 * [
 *     'title_key' => 'learning_laravel_title',    // of direct 'title' => '...'
 *     'description_key' => 'learning_laravel_desc',
 *     'progress' => 40, // 0-100
 *     'resources' => [
 *         ['name_key' => 'learning_resource_docs', 'url' => 'https://laravel.com/docs'],
 *         'Udemy Course' // strings blijven ook werken
 *     ],
 *     'timeline_key' => 'learning_laravel_timeline'
 * ]
 */

class SkillModel {
    /**
     * Education items per language. Keep empty to hide for now.
     */
    private $education = [
        'nl' => [
            // [
            //     'title_key' => 'education_hbo_title',
            //     'institution_key' => 'education_hbo_inst',
            //     'period_key' => 'education_hbo_period',
            //     'description_key' => 'education_hbo_desc',
            //     'skills' => [
            //         ['label_key' => 'education_hbo_skill_arch'],
            //         'Team Collaboration'
            //     ]
            // ]
        ],
        'en' => [
            // [
            //     'title_key' => 'education_hbo_title',
            //     'institution_key' => 'education_hbo_inst',
            //     'period_key' => 'education_hbo_period',
            //     'description_key' => 'education_hbo_desc',
            //     'skills' => [
            //         ['label_key' => 'education_hbo_skill_arch'],
            //         'Team Collaboration'
            //     ]
            // ]
        ]
    ];

    /**
     * Learning goals per language. Keep empty to hide for now.
     */
    private $learningGoals = [
        'nl' => [
            // [
            //     'title_key' => 'learning_laravel_title',
            //     'description_key' => 'learning_laravel_desc',
            //     'progress' => 40,
            //     'resources' => [
            //         ['name_key' => 'learning_resource_docs', 'url' => 'https://laravel.com/docs'],
            //         'YouTube Tutorials'
            //     ],
            //     'timeline_key' => 'learning_laravel_timeline'
            // ]
        ],
        'en' => [
            // [
            //     'title_key' => 'learning_laravel_title',
            //     'description_key' => 'learning_laravel_desc',
            //     'progress' => 40,
            //     'resources' => [
            //         ['name_key' => 'learning_resource_docs', 'url' => 'https://laravel.com/docs'],
            //         'YouTube Tutorials'
            //     ],
            //     'timeline_key' => 'learning_laravel_timeline'
            // ]
        ]
    ];

    /**
     * Get all skills with modal data
     * @return array All skills
     */
    public function getAllSkills() {
        return [
            // Programming Languages
            [
                'name' => 'PHP',
                'level' => 1,
                'category' => 'languages',
                'notes' => 'Object-oriented, Laravel, API development',
                'projects' => ['Portfolio Website', 'Laravel Project']
            ],
            [
                'name' => 'Java',
                'level' => 1,
                'category' => 'languages',
                'notes' => 'Minecraft plugins, Spring basics, Basic Projects',
                'projects' => []
            ],
            [
                'name' => 'JavaScript',
                'level' => 1,
                'category' => 'languages',
                'notes' => 'ES6+, DOM manipulation, AJAX',
                'projects' => ['Portfolio Website', 'Contract Companion', 'Lyrics Finder']
            ],
            [
                'name' => 'Python',
                'level' => 1,
                'category' => 'languages, Business Analytics',
                'notes' => 'Learning fundamentals, automation scripts',
                'projects' => []
            ],

            [
                'name' => 'SQL',
                'level' => 2,
                'category' => 'languages',
                'notes' => 'Oracle, Database',
                'projects' => ['Contract Companion']
            ],
            [
                'name' => 'HTML/CSS',
                'level' => 3,
                'category' => 'languages',
                'notes' => 'Semantic HTML, CSS Grid, Flexbox',
                'projects' => ['Portfolio Website', 'Contract Companion']
            ],

            // Frameworks & Tools
            [
                'name' => 'Laravel',
                'level' => 1,
                'category' => 'frameworks',
                'notes' => 'Currently learning MVC patterns',
                'projects' => []
            ],
            [
                'name' => 'Vue.js',
                'level' => 1,
                'category' => 'frameworks',
                'notes' => 'Learning reactive programming',
                'projects' => ['Contract Companion']
            ],
            [
                'name' => 'Spigot/Bukkit',
                'level' => 1,
                'category' => 'frameworks',
                'notes' => 'Advanced plugin development',
                'projects' => []
            ],

            // Tools
            [
                'name' => 'Git',
                'level' => 3,
                'category' => 'tools',
                'notes' => 'Version control, branching, collaboration',
                'projects' => ['All projects']
            ],
            [
                'name' => 'Linux',
                'level' => 1,
                'category' => 'tools',
                'notes' => 'Server management, bash scripting',
                'projects' => []
            ],
            [
                'name' => 'VS Code',
                'level' => 3,
                'category' => 'tools',
                'notes' => 'Extensions, debugging, Git integration',
                'projects' => ['All projects']
            ],
        ];
    }

    /**
     * Get skills by category
     * @param string $category Category name
     * @return array Filtered skills
     */
    public function getSkillsByCategory($category) {
        $skills = $this->getAllSkills();
        return array_filter($skills, function($skill) use ($category) {
            return $skill['category'] === $category;
        });
    }

    /**
     * Get level text (for display)
     * @param int $level Skill level (1-3)
     * @return string Level text
     */
    public function getLevelText($level) {
        switch($level) {
            case 1: return trans('skills_level_beginner');
            case 2: return trans('skills_level_intermediate');
            case 3: return trans('skills_level_advanced');
            default: return 'Unknown';
        }
    }

    /**
     * Get level percentage (for progress bar)
     * @param int $level Skill level (1-3)
     * @return float Percentage
     */
    public function getLevelPercentage($level) {
        return ($level / 3) * 100;
    }

    /**
     * Get modal data for a skill (JSON encoded)
     * @param array $skill Skill array
     * @return string JSON string for data-modal attribute
     */
    public function getModalData($skill) {
        return htmlspecialchars(json_encode($skill), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get education list for the current language.
     * Leave arrays empty to hide the section.
     *
     * @param string $lang Language key (e.g., 'nl' or 'en')
     * @return array
     */
    public function getEducation($lang = 'nl') {
        return $this->getLocalized($this->education, $lang);
    }

    /**
     * Get learning goals for the current language.
     * Leave arrays empty to hide the section.
     *
     * @param string $lang Language key (e.g., 'nl' or 'en')
     * @return array
     */
    public function getLearningGoals($lang = 'nl') {
        return $this->getLocalized($this->learningGoals, $lang);
    }

    /**
     * Build encoded modal payload for education items.
     * Accepts either a string title or an array (template above).
     */
    public function buildEducationModalData($item, $index) {
        $data = $this->normalizeEducationItem($item, $index);
        return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build encoded modal payload for learning goals.
     * Accepts either a string title or an array (template above).
     */
    public function buildLearningModalData($item, $index) {
        $data = $this->normalizeLearningItem($item, $index);
        return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Return localized content with a graceful fallback.
     */
    private function getLocalized(array $items, $lang) {
        if (array_key_exists($lang, $items)) {
            return $items[$lang];
        }

        return $items['en'] ?? reset($items) ?? [];
    }

    /**
     * Normalize education item to full modal-ready structure.
     */
    private function normalizeEducationItem($item, $index) {
        if (!is_array($item)) {
            $item = ['title' => $item];
        }

        $skills = $item['skills'] ?? [];

        return [
            'id' => $index,
            'title' => $this->resolveText($item, 'title'),
            'institution' => $this->resolveText($item, 'institution') ?: $this->resolveText($item, 'title'),
            'period' => $this->resolveText($item, 'period'),
            'description' => $this->resolveText($item, 'description'),
            'skills' => $this->resolveList($skills),
            'certificate_url' => $item['certificate_url'] ?? ''
        ];
    }

    /**
     * Normalize learning goal item to full modal-ready structure.
     */
    private function normalizeLearningItem($item, $index) {
        if (!is_array($item)) {
            $item = ['title' => $item];
        }

        return [
            'id' => $index,
            'title' => $this->resolveText($item, 'title'),
            'description' => $this->resolveText($item, 'description'),
            'progress' => $item['progress'] ?? null,
            'resources' => $this->resolveResources($item['resources'] ?? []),
            'timeline' => $this->resolveText($item, 'timeline')
        ];
    }

    /**
     * Resolve a text field, preferring direct value, falling back to translation key.
     */
    private function resolveText(array $item, $field) {
        if (!empty($item[$field])) {
            return $item[$field];
        }

        if (!empty($item["{$field}_key"])) {
            return trans($item["{$field}_key"]);
        }

        return '';
    }

    /**
     * Resolve a list of skills/strings with optional translation keys.
     */
    private function resolveList(array $items) {
        return array_map(function($entry) {
            if (is_array($entry)) {
                if (!empty($entry['label'])) {
                    return $entry['label'];
                }
                if (!empty($entry['label_key'])) {
                    return trans($entry['label_key']);
                }
            }
            return $entry;
        }, $items);
    }

    /**
     * Resolve resources with optional translated names.
     */
    private function resolveResources(array $resources) {
        return array_map(function($resource) {
            if (is_array($resource)) {
                $name = $resource['name'] ?? (!empty($resource['name_key']) ? trans($resource['name_key']) : '');
                return [
                    'name' => $name,
                    'url' => $resource['url'] ?? ''
                ];
            }
            return $resource;
        }, $resources);
    }
}
?>
