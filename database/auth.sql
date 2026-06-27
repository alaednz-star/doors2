CREATE DATABASE IF NOT EXISTS `door_showroom`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `admin_users` (
    `id`                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`                VARCHAR(200)     NOT NULL,
    `email`               VARCHAR(254)     NOT NULL,
    `password_hash`       VARCHAR(255)     NOT NULL,
    `role`                ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    `is_active`           TINYINT(1)       NOT NULL DEFAULT 1,
    `failed_login_count`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `locked_until`        DATETIME         NULL,
    `last_login_at`       DATETIME         NULL,
    `last_login_ip`       VARCHAR(45)      NULL,
    `created_at`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `remember_tokens` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_user_id`  INT UNSIGNED NOT NULL,
    `token_hash`     VARCHAR(255) NOT NULL,
    `expires_at`     DATETIME     NOT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    KEY `idx_user` (`admin_user_id`),
    KEY `idx_expires` (`expires_at`),
    CONSTRAINT `fk_rt_user`
        FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `key`           VARCHAR(150)      NOT NULL,
    `attempts`      SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `window_start`  DATETIME          NOT NULL,
    `blocked_until` DATETIME          NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_key` (`key`),
    KEY `idx_blocked_until` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_log` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_user_id` INT UNSIGNED    NULL,
    `action`        VARCHAR(120)    NOT NULL,
    `ip_address`    VARCHAR(45)     NULL,
    `user_agent`    VARCHAR(300)    NULL,
    `metadata`      JSON            NULL,
    `occurred_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_date` (`admin_user_id`, `occurred_at`),
    KEY `idx_action` (`action`),
    KEY `idx_date` (`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default super admin
-- Password: Admin@2025!  — CHANGE AFTER FIRST LOGIN
INSERT INTO `admin_users` (`name`, `email`, `password_hash`, `role`, `is_active`)
VALUES (
    'Super Admin',
    'admin@showroom.dz',
    '$2y$12$dGF74vrgSq565d0DKy4XuuUYZz5KnOgIorWw.Hzy0GxjHdV1J6lcu',
    'super_admin',
    1
);
