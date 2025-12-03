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
 */

class SkillModel {
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
}
?>