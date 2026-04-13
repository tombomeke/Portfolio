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

-- User profile fields (MySQL 5.7-safe and idempotent)
-- This block can be run multiple times without failing on duplicate columns.
DROP PROCEDURE IF EXISTS ensure_user_profile_columns;
DELIMITER $$
CREATE PROCEDURE ensure_user_profile_columns()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'birthday'
    ) THEN
        ALTER TABLE users ADD COLUMN birthday DATE NULL;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_photo_path'
    ) THEN
        ALTER TABLE users ADD COLUMN profile_photo_path VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'about'
    ) THEN
        ALTER TABLE users ADD COLUMN about TEXT NULL;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'public_profile'
    ) THEN
        ALTER TABLE users ADD COLUMN public_profile TINYINT(1) NOT NULL DEFAULT 1;
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'preferred_language'
    ) THEN
        ALTER TABLE users ADD COLUMN preferred_language VARCHAR(2) NOT NULL DEFAULT 'nl';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email_notifications'
    ) THEN
        ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) NOT NULL DEFAULT 1;
    END IF;
END$$
DELIMITER ;
CALL ensure_user_profile_columns();
DROP PROCEDURE IF EXISTS ensure_user_profile_columns;

-- Add 'user' role for public registrations (owner/admin unchanged)
ALTER TABLE users MODIFY COLUMN role ENUM('owner','admin','user') NOT NULL DEFAULT 'user';

-- User skills (public profile feature)
CREATE TABLE IF NOT EXISTS user_skills (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    name             VARCHAR(120) NOT NULL,
    category         VARCHAR(80)  NOT NULL,
    level            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    years_experience TINYINT UNSIGNED NULL,
    is_public        TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_public (user_id, is_public),
    INDEX idx_category (category),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Local ReadmeSync scan log (website-side audit/debug)
CREATE TABLE IF NOT EXISTS readmesync_scan_logs (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id              INT UNSIGNED NULL,
    username             VARCHAR(100) NULL,
    user_role            VARCHAR(20)  NULL,
    source_client        VARCHAR(64)  NOT NULL DEFAULT 'portfolio',
    source_user_id       VARCHAR(128) NULL,
    source_user_name     VARCHAR(128) NULL,
    repo_url             VARCHAR(512) NULL,
    success              TINYINT(1)   NOT NULL DEFAULT 0,
    http_code            INT          NULL,
    language             VARCHAR(32)  NULL,
    todo_count           INT          NULL,
    api_contract_version VARCHAR(80)  NULL,
    response_keys        VARCHAR(500) NULL,
    error_message        TEXT         NULL,
    created_at           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_repo (repo_url(191)),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reconcile existing readmesync_scan_logs schema (for older installs)
DROP PROCEDURE IF EXISTS ensure_readmesync_scan_log_schema;
DELIMITER $$
CREATE PROCEDURE ensure_readmesync_scan_log_schema()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND COLUMN_NAME = 'source_user_id'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD COLUMN source_user_id VARCHAR(128) NULL AFTER source_client;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND COLUMN_NAME = 'source_user_name'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD COLUMN source_user_name VARCHAR(128) NULL AFTER source_user_id;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND COLUMN_NAME = 'api_contract_version'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD COLUMN api_contract_version VARCHAR(80) NULL AFTER todo_count;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND COLUMN_NAME = 'response_keys'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD COLUMN response_keys VARCHAR(500) NULL AFTER api_contract_version;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND INDEX_NAME = 'idx_created_at'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD INDEX idx_created_at (created_at);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND INDEX_NAME = 'idx_repo'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD INDEX idx_repo (repo_url(191));
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readmesync_scan_logs' AND INDEX_NAME = 'idx_user'
    ) THEN
        ALTER TABLE readmesync_scan_logs ADD INDEX idx_user (user_id);
    END IF;
END$$
DELIMITER ;
CALL ensure_readmesync_scan_log_schema();
DROP PROCEDURE IF EXISTS ensure_readmesync_scan_log_schema;
