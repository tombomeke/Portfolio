-- Seed: migrate existing static projects to DB
-- Run this AFTER migrate.sql

INSERT INTO projects (slug, category, status, image_path, repo_url, demo_url, tech, sort_order) VALUES
(
    'rpg-manager',
    'cli',
    'development',
    'public/images/projects/p1.png',
    'https://github.com/tombomeke-ehb/RPGManager',
    'https://github.com/tombomeke-ehb/RPGManager/releases/latest/',
    '["C#",".NET","Object-Oriented Design","System.Text.Json","Console Application"]',
    1
),
(
    'portfolio-website',
    'web',
    'active',
    'public/images/projects/p2.png',
    'https://github.com/tombomeke/portfolio',
    NULL,
    '["PHP","JavaScript","CSS Grid","HTML5"]',
    2
);

-- NL translations
INSERT INTO project_translations (project_id, lang, title, description, long_description, features) VALUES
(
    (SELECT id FROM projects WHERE slug = 'rpg-manager'),
    'nl',
    'RPG Manager',
    'RPG Manager is een console-based role-playing game framework in C# met een modulair systeem voor werelden, locaties, helden en wapens. Inclusief JSON-saves en een uitbreidbare architectuur.',
    'Het project bestaat uit namespaces zoals <code>Characters</code>, <code>Weapons</code>, <code>Worlds</code>, <code>Locations</code>, <code>UI</code> en <code>Saves</code>. Spelers maken helden (bv. Warrior, Mage), kiezen wapens en verkennen werelden. Voortgang wordt opgeslagen via <code>System.Text.Json</code>.<br><br><strong>Belangrijkste kenmerken</strong><br>• Modulair systeem (makkelijk uitbreiden met nieuwe werelden/klassen/wapens)<br>• Wapen- en inventarissysteem met rarity, durability en upgrades<br>• Persistente opslag met JSON-saves<br>• Heldensysteem met levels/skills<br>• Wereldstructuur met moeilijkheidsgraden en unlockbare locaties',
    '["Modulaire architectuur","JSON-opslaan en laden","Wapenrarity & upgrades","Heldensysteem met levels","Uitbreidbare wereld/locatie-structuur"]'
),
(
    (SELECT id FROM projects WHERE slug = 'portfolio-website'),
    'nl',
    'Portfolio Website',
    'Deze responsive portfolio website gebouwd met vanilla PHP en moderne CSS. Features include project showcase, skill tracking en contact form.',
    'Een modern portfolio website gebouwd van scratch met PHP MVC architectuur. Bevat een geavanceerd modal systeem, meertalige ondersteuning (NL/EN), en een responsief design dat perfect werkt op alle apparaten.',
    '["Meertalige ondersteuning (NL/EN)","Universeel modal systeem","Responsive design","Contactformulier met validatie","Dark theme","Vloeiende animaties","SEO geoptimaliseerd"]'
);

-- EN translations
INSERT INTO project_translations (project_id, lang, title, description, long_description, features) VALUES
(
    (SELECT id FROM projects WHERE slug = 'rpg-manager'),
    'en',
    'RPG Manager',
    'RPG Manager is a console-based role-playing game framework in C# featuring a modular system for worlds, locations, heroes and weapons. Includes JSON saves and an extensible architecture.',
    'The project is organized into namespaces like <code>Characters</code>, <code>Weapons</code>, <code>Worlds</code>, <code>Locations</code>, <code>UI</code>, and <code>Saves</code>. Players create heroes (e.g., Warrior, Mage), choose weapons, and explore worlds. Progress is saved using <code>System.Text.Json</code>.<br><br><strong>Key features</strong><br>• Modular system (easily add worlds/classes/weapons)<br>• Weapon & inventory with rarity, durability, and upgrades<br>• Persistent storage via JSON saves<br>• Hero system with levels/skills<br>• World structure with difficulties and unlockable locations',
    '["Modular architecture","JSON save/load","Weapon rarity & upgrades","Hero system with levels","Extensible world/location structure"]'
),
(
    (SELECT id FROM projects WHERE slug = 'portfolio-website'),
    'en',
    'Portfolio Website',
    'This responsive portfolio website built with vanilla PHP and modern CSS. Features include project showcase, skill tracking and contact form.',
    'A modern portfolio website built from scratch with PHP MVC architecture. Features an advanced modal system, multi-language support (NL/EN), and a responsive design that works perfectly on all devices.',
    '["Multi-language support (NL/EN)","Universal modal system","Responsive design","Contact form with validation","Dark theme","Smooth animations","SEO optimized"]'
);
