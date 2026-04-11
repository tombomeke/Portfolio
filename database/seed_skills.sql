-- Seed: migrate existing static skills to DB
-- Run this AFTER migrate.sql

INSERT INTO skills (name, category, level, notes, projects, sort_order) VALUES
('HTML/CSS',    'languages',  3, 'Semantic HTML, CSS Grid, Flexbox',                    '["Portfolio Website","Contract Companion"]', 1),
('JavaScript',  'languages',  1, 'ES6+, DOM manipulation, AJAX',                        '["Portfolio Website","Contract Companion","Lyrics Finder"]', 2),
('PHP',         'languages',  1, 'Object-oriented, Laravel, API development',            '["Portfolio Website","Laravel Project"]', 3),
('SQL',         'languages',  2, 'Oracle, Database',                                     '["Contract Companion"]', 4),
('Java',        'languages',  1, 'Minecraft plugins, Spring basics, Basic Projects',     '[]', 5),
('Python',      'languages',  1, 'Learning fundamentals, automation scripts, Business Analytics', '[]', 6),
('C#',          'languages',  2, 'Console apps, .NET, Object-Oriented Design',           '["RPG Manager","ReadmeSync"]', 7),
('Laravel',     'frameworks', 1, 'Currently learning MVC patterns',                      '[]', 10),
('Vue.js',      'frameworks', 1, 'Learning reactive programming',                        '["Contract Companion"]', 11),
('Spigot/Bukkit','frameworks',1, 'Advanced plugin development',                          '[]', 12),
('Git',         'tools',      3, 'Version control, branching, collaboration',            '["All projects"]', 20),
('VS Code',     'tools',      3, 'Extensions, debugging, Git integration',               '["All projects"]', 21),
('Linux',       'tools',      1, 'Server management, bash scripting',                    '[]', 22);
