-- migrate_v2.sql — Run AFTER migrate.sql
-- Adds: tags, news_comments, activity_logs, site_settings, user profile fields

-- Tags (many-to-many with news_items)
CREATE TABLE IF NOT EXISTS tags (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60)  NOT NULL UNIQUE,
    slug       VARCHAR(60)  NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_item_tag (
    news_item_id INT UNSIGNED NOT NULL,
    tag_id       INT UNSIGNED NOT NULL,
    PRIMARY KEY (news_item_id, tag_id),
    FOREIGN KEY (news_item_id) REFERENCES news_items(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)       REFERENCES tags(id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- News comments (requires auth)
CREATE TABLE IF NOT EXISTS news_comments (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    news_item_id INT UNSIGNED NOT NULL,
    user_id      INT UNSIGNED NOT NULL,
    body         TEXT         NOT NULL,
    is_approved  TINYINT(1)   NOT NULL DEFAULT 0,
    approved_at  DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_news_approved (news_item_id, is_approved, created_at),
    FOREIGN KEY (news_item_id) REFERENCES news_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,
    action      VARCHAR(50)  NOT NULL,
    model_type  VARCHAR(100) NULL,
    model_id    INT UNSIGNED NULL,
    description TEXT         NOT NULL,
    properties  JSON         NULL,
    ip_address  VARCHAR(45)  NULL,
    user_agent  VARCHAR(500) NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action     (action),
    INDEX idx_model      (model_type, model_id),
    INDEX idx_created    (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings
CREATE TABLE IF NOT EXISTS site_settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`       VARCHAR(100) NOT NULL UNIQUE,
    value       TEXT         NULL,
    type        VARCHAR(20)  NOT NULL DEFAULT 'string',
    `group`     VARCHAR(50)  NOT NULL DEFAULT 'general',
    label       VARCHAR(255) NOT NULL,
    description TEXT         NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User profile fields
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS birthday           DATE         NULL AFTER email,
    ADD COLUMN IF NOT EXISTS profile_photo_path VARCHAR(255) NULL AFTER birthday,
    ADD COLUMN IF NOT EXISTS about              TEXT         NULL AFTER profile_photo_path,
    ADD COLUMN IF NOT EXISTS public_profile     TINYINT(1)   NOT NULL DEFAULT 1 AFTER about,
    ADD COLUMN IF NOT EXISTS preferred_language VARCHAR(2)   NOT NULL DEFAULT 'nl' AFTER public_profile;

-- Add 'user' role for public registrations (owner/admin unchanged)
ALTER TABLE users MODIFY COLUMN role ENUM('owner','admin','user') NOT NULL DEFAULT 'admin';
