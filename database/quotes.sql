USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `quote_requests` (
    `id`               INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `reference`        VARCHAR(20)         NOT NULL,

    `customer_name`    VARCHAR(120)        NOT NULL,
    `customer_phone`   VARCHAR(30)         NOT NULL,
    `customer_email`   VARCHAR(180)        NULL,
    `customer_city`    VARCHAR(100)        NULL,
    `notes`            TEXT                NULL,

    `product_id`       INT UNSIGNED        NULL,
    `material_id`      INT UNSIGNED        NULL,
    `finish_id`        INT UNSIGNED        NULL,
    `color_id`         INT UNSIGNED        NULL,
    `door_type_id`     INT UNSIGNED        NULL,
    `width_mm`         SMALLINT UNSIGNED   NULL,
    `height_mm`        SMALLINT UNSIGNED   NULL,
    `handle`           VARCHAR(120)        NULL,
    `features_json`    JSON                NULL,
    `final_price`      DECIMAL(10,2)       NULL,
    `currency`         CHAR(3)             NOT NULL DEFAULT 'DZD',

    `status`           ENUM(
                           'new',
                           'contacted',
                           'quotation_sent',
                           'in_progress',
                           'confirmed',
                           'completed',
                           'cancelled'
                       )                   NOT NULL DEFAULT 'new',

    `status_notes`     TEXT                NULL,
    `assigned_to`      INT UNSIGNED        NULL,
    `submitted_at`     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_qr_reference`    (`reference`),
    KEY `idx_qr_status`             (`status`),
    KEY `idx_qr_submitted`          (`submitted_at`),
    KEY `idx_qr_customer_phone`     (`customer_phone`),
    KEY `idx_qr_assigned`           (`assigned_to`),

    CONSTRAINT `fk_qr_product`      FOREIGN KEY (`product_id`)   REFERENCES `products`   (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_qr_material`     FOREIGN KEY (`material_id`)  REFERENCES `materials`  (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_qr_finish`       FOREIGN KEY (`finish_id`)    REFERENCES `finishes`   (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_qr_color`        FOREIGN KEY (`color_id`)     REFERENCES `colors`     (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_qr_door_type`    FOREIGN KEY (`door_type_id`) REFERENCES `door_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_qr_assigned`     FOREIGN KEY (`assigned_to`)  REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quote_status_log` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `quote_id`    INT UNSIGNED  NOT NULL,
    `from_status` VARCHAR(30)   NULL,
    `to_status`   VARCHAR(30)   NOT NULL,
    `notes`       TEXT          NULL,
    `changed_by`  INT UNSIGNED  NULL,
    `changed_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_qsl_quote`   (`quote_id`),
    KEY `idx_qsl_changed` (`changed_at`),

    CONSTRAINT `fk_qsl_quote`      FOREIGN KEY (`quote_id`)   REFERENCES `quote_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_qsl_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `admin_users`    (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
