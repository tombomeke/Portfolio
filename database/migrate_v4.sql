-- migrate_v4.sql — data corrections (run once on production)
-- Sets demo_url to NULL for the Portfolio Website project so the demo button
-- does not render. The previous value (https://tomdekoning.nl) was a placeholder.

UPDATE projects
   SET demo_url = NULL
 WHERE slug = 'portfolio-website';
