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

-- FAQ tabellen
CREATE TABLE IF NOT EXISTS faq_categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faq_category_translations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faq_category_id INT UNSIGNED NOT NULL,
    lang            VARCHAR(5)   NOT NULL,
    name            VARCHAR(255) NOT NULL,
    UNIQUE KEY uq_cat_lang (faq_category_id, lang),
    FOREIGN KEY (faq_category_id) REFERENCES faq_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faq_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faq_category_id INT UNSIGNED NOT NULL,
    sort_order      INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (faq_category_id) REFERENCES faq_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS faq_item_translations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faq_item_id INT UNSIGNED NOT NULL,
    lang        VARCHAR(5)   NOT NULL,
    question    VARCHAR(500) NOT NULL,
    answer      TEXT         NOT NULL,
    UNIQUE KEY uq_item_lang (faq_item_id, lang),
    FOREIGN KEY (faq_item_id) REFERENCES faq_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Gebruikers (owner + admin rollen)
-- Aanmaken via ?page=setup (eerste keer) of ?page=admin&section=users
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('owner','admin') NOT NULL DEFAULT 'admin',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Projecten (DB-driven, vervangt statische array)
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS projects (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug       VARCHAR(120) NOT NULL UNIQUE,
    category   VARCHAR(50)  NOT NULL,
    status     VARCHAR(50)  NULL,
    image_path VARCHAR(255) NULL,
    repo_url   VARCHAR(255) NULL,
    demo_url   VARCHAR(255) NULL,
    tech       JSON         NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category  (category),
    INDEX idx_sort      (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS project_translations (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id       INT UNSIGNED NOT NULL,
    lang             VARCHAR(5)   NOT NULL,
    title            VARCHAR(255) NOT NULL,
    description      TEXT         NOT NULL,
    long_description TEXT         NULL,
    features         JSON         NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_lang (project_id, lang),
    INDEX idx_lang_title (lang, title),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Contact berichten (opgeslagen in DB + inbox)
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    subject     VARCHAR(255) NULL,
    message     TEXT         NOT NULL,
    read_at     DATETIME     NULL,
    admin_reply TEXT         NULL,
    replied_at  DATETIME     NULL,
    ip_address  VARCHAR(45)  NULL,
    user_agent  TEXT         NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email   (email),
    INDEX idx_read    (read_at),
    INDEX idx_replied (replied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Skills (Dev Life pagina)
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS skills (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    category   VARCHAR(50)  NOT NULL,   -- languages, frameworks, database, tools
    level      TINYINT UNSIGNED NOT NULL DEFAULT 1,  -- 1=beginner, 2=intermediate, 3=advanced
    notes      TEXT         NULL,
    projects   JSON         NULL,       -- array van projectnamen
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_sort     (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Opleidingen & certificaten (Dev Life pagina)
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS education_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sort_order      INT UNSIGNED NOT NULL DEFAULT 0,
    certificate_url VARCHAR(255) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS education_item_translations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    education_item_id INT UNSIGNED NOT NULL,
    lang              VARCHAR(5)   NOT NULL,
    title             VARCHAR(255) NOT NULL,
    institution       VARCHAR(255) NULL,
    period            VARCHAR(100) NULL,
    description       TEXT         NULL,
    skills_list       JSON         NULL,   -- array van skillnamen
    UNIQUE KEY uq_edu_lang (education_item_id, lang),
    FOREIGN KEY (education_item_id) REFERENCES education_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════
-- Leerdoelen (Dev Life pagina)
-- ═══════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS learning_goals (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    progress   TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- 0-100
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS learning_goal_translations (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    learning_goal_id INT UNSIGNED NOT NULL,
    lang             VARCHAR(5)   NOT NULL,
    title            VARCHAR(255) NOT NULL,
    description      TEXT         NULL,
    timeline         VARCHAR(100) NULL,
    resources        JSON         NULL,   -- array van {name, url} objecten
    UNIQUE KEY uq_goal_lang (learning_goal_id, lang),
    FOREIGN KEY (learning_goal_id) REFERENCES learning_goals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
