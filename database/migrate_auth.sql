-- ============================================================
-- Auth-Migration: organizations, users, sessions
-- review_replies bekommt org_id
-- ============================================================

-- 1. Bestehende Reviewdaten leeren (dev-Daten, kein Verlust)
TRUNCATE TABLE review_replies;

-- 2. Organisationen (Mandanten)
CREATE TABLE IF NOT EXISTS `organizations` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(255)    NOT NULL,
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Benutzer
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `org_id`        INT UNSIGNED    NOT NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `role`          ENUM('owner','member') NOT NULL DEFAULT 'owner',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE  KEY `uq_email`  (`email`),
    INDEX   `idx_org`       (`org_id`),
    CONSTRAINT `fk_users_org` FOREIGN KEY (`org_id`)
        REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. org_id in review_replies (nach TRUNCATE sicher als NOT NULL)
ALTER TABLE `review_replies`
    ADD COLUMN `org_id` INT UNSIGNED NOT NULL AFTER `id`,
    ADD INDEX  `idx_rr_org` (`org_id`),
    ADD CONSTRAINT `fk_rr_org` FOREIGN KEY (`org_id`)
        REFERENCES `organizations`(`id`) ON DELETE CASCADE;
