-- ============================================================
-- Migration 002: departments
-- ============================================================

CREATE TABLE IF NOT EXISTS `departments` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)    NOT NULL,
    `description` VARCHAR(500)    NULL DEFAULT NULL,
    `created_at`  DATETIME        NOT NULL,
    `updated_at`  DATETIME        NOT NULL,
    `deleted_at`  DATETIME        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_departments_name`    (`name`),
    INDEX `idx_departments_deleted` (`deleted_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
