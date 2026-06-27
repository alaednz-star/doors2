USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `door_types` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)      NOT NULL,
    `slug`          VARCHAR(120)      NOT NULL,
    `description`   TEXT              NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_door_types_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `finishes` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(100)      NOT NULL,
    `slug`          VARCHAR(120)      NOT NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_finishes_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_rules` (
    `id`             INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(180)        NOT NULL,
    `dimension_type` ENUM('fixed','per_sqm','per_lm') NOT NULL DEFAULT 'fixed',

    `product_id`     INT UNSIGNED        NULL COMMENT 'NULL = applies to all',
    `material_id`    INT UNSIGNED        NULL,
    `color_id`       INT UNSIGNED        NULL,
    `finish_id`      INT UNSIGNED        NULL,
    `door_type_id`   INT UNSIGNED        NULL,
    `category_id`    INT UNSIGNED        NULL,
    `collection_id`  INT UNSIGNED        NULL,

    `width_min_mm`   SMALLINT UNSIGNED   NULL,
    `width_max_mm`   SMALLINT UNSIGNED   NULL,
    `height_min_mm`  SMALLINT UNSIGNED   NULL,
    `height_max_mm`  SMALLINT UNSIGNED   NULL,

    `base_price`     DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    `price_modifier` DECIMAL(10,4)       NOT NULL DEFAULT 0.0000 COMMENT 'Additive amount',
    `multiplier`     DECIMAL(6,4)        NOT NULL DEFAULT 1.0000 COMMENT 'Multiplicative factor',

    `priority`       SMALLINT UNSIGNED   NOT NULL DEFAULT 0 COMMENT 'Higher wins when rules overlap',
    `is_active`      TINYINT(1)          NOT NULL DEFAULT 1,

    `valid_from`     DATE                NULL,
    `valid_until`    DATE                NULL,

    `created_by`     INT UNSIGNED        NULL,
    `created_at`     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_pr_product`    (`product_id`),
    KEY `idx_pr_material`   (`material_id`),
    KEY `idx_pr_color`      (`color_id`),
    KEY `idx_pr_finish`     (`finish_id`),
    KEY `idx_pr_door_type`  (`door_type_id`),
    KEY `idx_pr_priority`   (`priority`),
    KEY `idx_pr_active`     (`is_active`),

    CONSTRAINT `fk_pr_product`    FOREIGN KEY (`product_id`)    REFERENCES `products`   (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_material`   FOREIGN KEY (`material_id`)   REFERENCES `materials`  (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_color`      FOREIGN KEY (`color_id`)      REFERENCES `colors`     (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_finish`     FOREIGN KEY (`finish_id`)     REFERENCES `finishes`   (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_door_type`  FOREIGN KEY (`door_type_id`)  REFERENCES `door_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_category`   FOREIGN KEY (`category_id`)   REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pr_created_by` FOREIGN KEY (`created_by`)    REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `optional_features` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120)      NOT NULL,
    `slug`          VARCHAR(140)      NOT NULL,
    `description`   TEXT              NULL,
    `price`         DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `price_type`    ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_features_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `price_calculations` (
    `id`             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `session_token`  CHAR(64)          NOT NULL,
    `product_id`     INT UNSIGNED      NULL,
    `material_id`    INT UNSIGNED      NULL,
    `color_id`       INT UNSIGNED      NULL,
    `finish_id`      INT UNSIGNED      NULL,
    `door_type_id`   INT UNSIGNED      NULL,
    `width_mm`       SMALLINT UNSIGNED NULL,
    `height_mm`      SMALLINT UNSIGNED NULL,
    `features_json`  JSON              NULL,
    `rules_applied`  JSON              NULL,
    `base_price`     DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `options_price`  DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `total_price`    DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
    `currency`       CHAR(3)           NOT NULL DEFAULT 'DZD',
    `created_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_calc_token`   (`session_token`),
    KEY `idx_calc_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `door_types` (`name`, `slug`, `display_order`) VALUES
('Interior',       'interior',        1),
('Exterior',       'exterior',        2),
('Sliding',        'sliding',         3),
('Pivot',          'pivot',           4),
('Bi-Fold',        'bi-fold',         5),
('French Double',  'french-double',   6);

INSERT IGNORE INTO `finishes` (`name`, `slug`, `display_order`) VALUES
('Matte',          'matte',           1),
('Gloss',          'gloss',           2),
('Satin',          'satin',           3),
('Brushed',        'brushed',         4),
('Lacquered',      'lacquered',       5),
('Natural Oiled',  'natural-oiled',   6);

INSERT IGNORE INTO `optional_features` (`name`, `slug`, `description`, `price`, `price_type`, `display_order`) VALUES
('Smart Lock',         'smart-lock',       'Electronic smart lock with app control',    180.00, 'fixed',   1),
('Double Glazing',     'double-glazing',   'Thermal double-glazed glass panel',         220.00, 'fixed',   2),
('Acoustic Seals',     'acoustic-seals',   'Sound-dampening perimeter seals',            95.00, 'fixed',   3),
('Security Bar',       'security-bar',     'Reinforced security bar kit',               140.00, 'fixed',   4),
('Soft Close',         'soft-close',       'Hydraulic soft-close hinge set',             60.00, 'fixed',   5),
('Custom RAL Color',   'custom-ral',       'Any RAL color, matched to spec',            150.00, 'fixed',   6),
('Premium Finish',     'premium-finish',   'Upgrade to premium surface treatment',       10.00, 'percent', 7);
