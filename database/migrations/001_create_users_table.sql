-- ============================================================
-- Migration 001: users
-- System accounts (backoffice login)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100)    NOT NULL,
    `email`      VARCHAR(191)    NOT NULL,
    `password`   VARCHAR(255)    NOT NULL,
    `role`       ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    `created_at` DATETIME        NOT NULL,
    `updated_at` DATETIME        NOT NULL,
    `deleted_at` DATETIME        NULL DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE  KEY `uq_users_email`      (`email`),
    INDEX        `idx_users_role`     (`role`),
    INDEX        `idx_users_deleted`  (`deleted_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Default super_admin (password: password)
-- Change this password immediately after first login!
INSERT INTO `users` (`name`, `email`, `password`, `role`, `created_at`, `updated_at`)
VALUES (
    'Super Admin',
    'superadmin@aurex.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'super_admin',
    NOW(),
    NOW()
);
