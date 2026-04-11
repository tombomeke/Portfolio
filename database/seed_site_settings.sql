-- seed_site_settings.sql — Run AFTER migrate_v2.sql
-- Default site settings (same as Laravel SiteSettingsSeeder)

INSERT INTO site_settings (`key`, value, type, `group`, label, description) VALUES
-- General
('site_name',           'Portfolio',                                            'string',  'general',  'Site Name',              'The name of your website'),
('site_description',    'Full-stack Developer portfolio',                       'text',    'general',  'Site Description',       'Short description for SEO'),
('maintenance_mode',    '0',                                                    'boolean', 'general',  'Maintenance Mode',       'Show a maintenance page to visitors'),
('maintenance_message', 'We are currently performing maintenance. Check back soon.', 'text', 'general', 'Maintenance Message', 'Message shown during maintenance'),
-- Features
('comments_enabled',          '1', 'boolean', 'features', 'Enable Comments',          'Allow users to comment on news articles'),
('comments_require_approval', '1', 'boolean', 'features', 'Comments Require Approval','New comments must be approved by admin'),
('contact_form_enabled',      '1', 'boolean', 'features', 'Enable Contact Form',      'Allow visitors to send contact messages'),
('public_profiles_enabled',   '1', 'boolean', 'features', 'Enable Public Profiles',   'Allow viewing user profiles via ?page=profile&u=username'),
-- Contact
('admin_email',          'tom1dekoning@gmail.com', 'string', 'contact', 'Admin Email',           'Email for admin notifications'),
('contact_email',        'tom1dekoning@gmail.com', 'string', 'contact', 'Contact Email',         'Public contact email address'),
('contact_subject_prefix','[Portfolio] ',          'string', 'contact', 'Subject Prefix',        'Prefix added to email subjects'),
-- Social
('social_github',    'https://github.com/tombomeke',                         'string', 'social', 'GitHub URL',    'Your GitHub profile URL'),
('social_linkedin',  'https://www.linkedin.com/in/tom-dekoning-567523352/', 'string', 'social', 'LinkedIn URL',  'Your LinkedIn profile URL'),
('social_twitter',   '',                                                     'string', 'social', 'Twitter/X URL', 'Your Twitter/X profile URL'),
('social_instagram', '',                                                     'string', 'social', 'Instagram URL', 'Your Instagram profile URL'),
-- Security
('max_login_attempts',           '5', 'integer', 'security', 'Max Login Attempts',          'Login throttle limit'),
('contact_rate_limit_enabled',   '1', 'boolean', 'security', 'Enable Contact Rate Limit',   'Protect contact form from spam'),
('contact_rate_limit_per_minute','5', 'integer', 'security', 'Contact Rate Limit/minute',   'Max submissions per minute per IP'),
-- UX
('default_language',    'nl', 'string',  'ux', 'Default Language',   'Default UI language (nl/en)'),
('show_cookie_notice',  '0',  'boolean', 'ux', 'Show Cookie Notice', 'Show cookie banner on first visit')
ON DUPLICATE KEY UPDATE value = VALUES(value);
