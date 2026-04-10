-- Voer dit éénmalig uit op je Combell MySQL database
-- Kopieer en plak in phpMyAdmin of via SSH

CREATE TABLE IF NOT EXISTS news_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_path   VARCHAR(255) NULL,
    published_at DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_item_translations (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    news_item_id INT UNSIGNED NOT NULL,
    lang         VARCHAR(5)   NOT NULL,   -- 'nl' of 'en'
    title        VARCHAR(255) NOT NULL,
    content      TEXT         NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_item_lang (news_item_id, lang),
    INDEX idx_lang_title (lang, title),
    FOREIGN KEY (news_item_id) REFERENCES news_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
