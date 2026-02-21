-- ============================================================
-- Migration 003: positions
-- level: 1 = entry, 2 = junior, 3 = senior, 4 = lead, 5 = manager+
-- ============================================================

CREATE TABLE IF NOT EXISTS `positions` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)    NOT NULL,
    `level`      TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME        NOT NULL,
    `updated_at` DATETIME        NOT NULL,
    `deleted_at` DATETIME        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_positions_level`   (`level`),
    INDEX `idx_positions_deleted` (`deleted_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
