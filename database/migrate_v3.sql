-- migrate_v3.sql
-- Multi-image gallery + project roadmap DB storage
-- MySQL 5.7 compatible: no IF NOT EXISTS on columns, no JSON_TABLE
-- Run after migrate_v2.sql

-- ── project_images ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS project_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    caption    VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_sort (project_id, sort_order),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── project_roadmap_items ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS project_roadmap_items (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id           INT UNSIGNED NOT NULL,
    file                 VARCHAR(500) NOT NULL DEFAULT '',
    line                 INT UNSIGNED NOT NULL DEFAULT 0,
    text                 TEXT NOT NULL,
    status               VARCHAR(20) NOT NULL DEFAULT 'open',
    priority             VARCHAR(20) NOT NULL DEFAULT 'normal',
    last_seen_at         DATETIME NULL,
    api_contract_version VARCHAR(80) NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_status   (project_id, status),
    INDEX idx_project_priority (project_id, priority),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── project_sync_log ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS project_sync_log (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id           INT UNSIGNED NOT NULL,
    item_count           INT UNSIGNED NOT NULL DEFAULT 0,
    api_contract_version VARCHAR(80) NULL,
    success              TINYINT(1) NOT NULL DEFAULT 1,
    error_message        TEXT NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project (project_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
