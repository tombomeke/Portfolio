<?php
// ============================================
// FILE 1: /app/Models/ProjectModel.php
// ============================================
class ProjectModel {
    public function getAllProjects() {
        // Voor MVP: static data, later: database
        return [
            [
                'id' => 1,
                'title' => 'Minecraft Economy Plugin',
                'description' => 'Een complete economie plugin voor Minecraft servers met shops, banking en trading systeem. Ondersteunt meerdere valuta\'s en een dynamische markt.',
                'tech' => ['Java', 'Spigot API', 'MySQL', 'Maven'],
                'repo_url' => 'https://github.com/jouwusername/mc-economy',
                'demo_url' => null,
                'image' => 'public/images/projects/mc-economy.jpg',
                'category' => 'minecraft'
            ],
            [
                'id' => 2,
                'title' => 'Portfolio Website',
                'description' => 'Deze responsive portfolio website gebouwd met vanilla PHP en moderne CSS. Features include project showcase, skill tracking en contact form.',
                'tech' => ['PHP', 'JavaScript', 'CSS Grid', 'HTML5'],
                'repo_url' => 'https://github.com/jouwusername/portfolio',
                'demo_url' => 'https://jouwdomain.nl',
                'image' => 'public/images/projects/portfolio.jpg',
                'category' => 'web'
            ],
            [
                'id' => 3,
                'title' => 'Task Manager API',
                'description' => 'RESTful API voor taakbeheer met JWT authenticatie en real-time updates via WebSockets. Ondersteunt team collaboration.',
                'tech' => ['PHP', 'JWT', 'WebSockets', 'PostgreSQL'],
                'repo_url' => 'https://github.com/jouwusername/task-api',
                'demo_url' => null,
                'image' => 'public/images/projects/task-api.jpg',
                'category' => 'api'
            ],
            [
                'id' => 4,
                'title' => 'Minecraft Minigames',
                'description' => 'Collection van populaire minigames zoals BedWars, SkyWars en Murder Mystery. Volledig configureerbaar via YAML files.',
                'tech' => ['Java', 'Bukkit API', 'Redis', 'MongoDB'],
                'repo_url' => 'https://github.com/jouwusername/minigames',
                'demo_url' => null,
                'image' => 'public/images/projects/minigames.jpg',
                'category' => 'minecraft'
            ],
            [
                'id' => 5,
                'title' => 'E-commerce Dashboard',
                'description' => 'Admin dashboard voor e-commerce platform met analytics, inventory management en order processing.',
                'tech' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Chart.js'],
                'repo_url' => 'https://github.com/jouwusername/ecommerce-dash',
                'demo_url' => 'https://demo.jouwdomain.nl',
                'image' => 'public/images/projects/ecommerce.jpg',
                'category' => 'web'
            ]
        ];
    }

    public function getProjectsByCategory($category) {
        $projects = $this->getAllProjects();
        return array_filter($projects, function($project) use ($category) {
            return $project['category'] === $category;
        });
    }

    public function getProjectById($id) {
        $projects = $this->getAllProjects();
        foreach ($projects as $project) {
            if ($project['id'] == $id) {
                return $project;
            }
        }
        return null;
    }
}
?>