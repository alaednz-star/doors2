USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `saved_configurations` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `token`         CHAR(64)          NOT NULL,
    `name`          VARCHAR(120)      NULL,
    `config_json`   JSON              NOT NULL,
    `total_price`   DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `currency`      CHAR(3)           NOT NULL DEFAULT 'DZD',
    `ip`            VARCHAR(45)       NULL,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`    DATETIME          NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_saved_configs_token` (`token`),
    KEY `idx_sc_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quote_requests` (
    `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `reference`       VARCHAR(20)   NOT NULL,
    `full_name`       VARCHAR(120)  NOT NULL,
    `email`           VARCHAR(254)  NOT NULL,
    `phone`           VARCHAR(30)   NULL,
    `message`         TEXT          NULL,
    `config_json`     JSON          NOT NULL,
    `total_price`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency`        CHAR(3)       NOT NULL DEFAULT 'DZD',
    `status`          ENUM('new','reviewing','quoted','won','lost') NOT NULL DEFAULT 'new',
    `ip`              VARCHAR(45)   NULL,
    `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_qr_reference` (`reference`),
    KEY `idx_qr_email`   (`email`),
    KEY `idx_qr_status`  (`status`),
    KEY `idx_qr_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
