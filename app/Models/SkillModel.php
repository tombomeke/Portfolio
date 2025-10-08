<?php
// ============================================
// FILE 2: /app/Models/SkillModel.php
// ============================================
class SkillModel {
    public function getAllSkills() {
        return [
            // Programming Languages
            ['name' => 'PHP', 'level' => 3, 'category' => 'languages', 'notes' => 'Object-oriented, Laravel, API development'],
            ['name' => 'Java', 'level' => 3, 'category' => 'languages', 'notes' => 'Minecraft plugins, Spring basics'],
            ['name' => 'JavaScript', 'level' => 2, 'category' => 'languages', 'notes' => 'ES6+, DOM manipulation, AJAX'],
            ['name' => 'Python', 'level' => 1, 'category' => 'languages', 'notes' => 'Learning fundamentals, automation scripts'],
            ['name' => 'SQL', 'level' => 2, 'category' => 'languages', 'notes' => 'MySQL, PostgreSQL, query optimization'],
            ['name' => 'HTML/CSS', 'level' => 3, 'category' => 'languages', 'notes' => 'Semantic HTML, CSS Grid, Flexbox'],

            // Frameworks & Tools
            ['name' => 'Laravel', 'level' => 1, 'category' => 'frameworks', 'notes' => 'Currently learning MVC patterns'],
            ['name' => 'React', 'level' => 1, 'category' => 'frameworks', 'notes' => 'Basic components and hooks'],
            ['name' => 'Vue.js', 'level' => 1, 'category' => 'frameworks', 'notes' => 'Learning reactive programming'],
            ['name' => 'Spigot/Bukkit', 'level' => 3, 'category' => 'frameworks', 'notes' => 'Advanced plugin development'],
            ['name' => 'Bootstrap', 'level' => 2, 'category' => 'frameworks', 'notes' => 'Responsive design, components'],

            // Database & DevOps
            ['name' => 'MySQL', 'level' => 2, 'category' => 'database', 'notes' => 'Database design, optimization'],
            ['name' => 'MongoDB', 'level' => 1, 'category' => 'database', 'notes' => 'NoSQL basics, CRUD operations'],
            ['name' => 'Redis', 'level' => 1, 'category' => 'database', 'notes' => 'Caching, session storage'],

            // Tools
            ['name' => 'Git', 'level' => 2, 'category' => 'tools', 'notes' => 'Version control, branching, collaboration'],
            ['name' => 'Docker', 'level' => 1, 'category' => 'tools', 'notes' => 'Containerization basics'],
            ['name' => 'Linux', 'level' => 2, 'category' => 'tools', 'notes' => 'Server management, bash scripting'],
            ['name' => 'VS Code', 'level' => 3, 'category' => 'tools', 'notes' => 'Extensions, debugging, Git integration'],
        ];
    }

    public function getSkillsByCategory($category) {
        $skills = $this->getAllSkills();
        return array_filter($skills, function($skill) use ($category) {
            return $skill['category'] === $category;
        });
    }

    public function getLevelText($level) {
        switch($level) {
            case 1: return 'Leer';
            case 2: return 'Basis';
            case 3: return 'Goed';
            default: return 'Onbekend';
        }
    }

    public function getLevelPercentage($level) {
        return ($level / 3) * 100;
    }
}
?>