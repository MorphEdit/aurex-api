-- ============================================================
-- Migration 004: employees
-- Run AFTER migrations 002 and 003
-- ============================================================

CREATE TABLE IF NOT EXISTS `employees` (
    `id`          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `emp_code`    VARCHAR(20)       NOT NULL COMMENT 'e.g. EMP0001',
    `full_name`   VARCHAR(150)      NOT NULL,
    `email`       VARCHAR(191)      NULL DEFAULT NULL,
    `phone`       VARCHAR(20)       NULL DEFAULT NULL,
    `dept_id`     INT UNSIGNED      NULL DEFAULT NULL,
    `position_id` INT UNSIGNED      NULL DEFAULT NULL,
    `hire_date`   DATE              NOT NULL,
    `salary`      DECIMAL(15, 2)    NOT NULL DEFAULT 0.00,
    `status`      ENUM('active', 'inactive', 'resigned') NOT NULL DEFAULT 'active',
    `created_at`  DATETIME          NOT NULL,
    `updated_at`  DATETIME          NOT NULL,
    `deleted_at`  DATETIME          NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE  KEY `uq_employees_emp_code`   (`emp_code`),
    INDEX        `idx_employees_email`    (`email`),
    INDEX        `idx_employees_dept`     (`dept_id`),
    INDEX        `idx_employees_position` (`position_id`),
    INDEX        `idx_employees_status`   (`status`),
    INDEX        `idx_employees_deleted`  (`deleted_at`),

    CONSTRAINT `fk_employees_dept`
        FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,

    CONSTRAINT `fk_employees_position`
        FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
