CREATE TABLE IF NOT EXISTS `review_replies` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `review_text`  TEXT            NOT NULL,
    `industry`     VARCHAR(50)     NOT NULL,
    `stars`        TINYINT         NOT NULL,
    `tone`         VARCHAR(30)     NOT NULL,
    `reply_1`      TEXT            NOT NULL,
    `reply_2`      TEXT            NOT NULL,
    `reply_3`      TEXT            NOT NULL,
    `risk_level`   ENUM('low','medium','high') NOT NULL DEFAULT 'low',
    `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_industry` (`industry`),
    INDEX `idx_stars` (`stars`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
