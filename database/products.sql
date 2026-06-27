USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `collections` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120)      NOT NULL,
    `slug`          VARCHAR(140)      NOT NULL,
    `description`   TEXT              NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_collections_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `materials` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(80)       NOT NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_materials_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `colors` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(80)       NOT NULL,
    `hex`           CHAR(7)           NULL COMMENT 'e.g. #2C2C2C',
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_colors_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `id`             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(180)      NOT NULL,
    `slug`           VARCHAR(200)      NOT NULL,
    `sku`            VARCHAR(60)       NULL,
    `description`    TEXT              NULL,
    `dimensions`     VARCHAR(120)      NULL COMMENT 'e.g. W900 × H2100 mm',
    `is_featured`    TINYINT(1)        NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)        NOT NULL DEFAULT 1,
    `display_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `category_id`    INT UNSIGNED      NULL,
    `collection_id`  INT UNSIGNED      NULL,
    `created_by`     INT UNSIGNED      NULL,
    `created_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_products_slug` (`slug`),
    UNIQUE KEY `uk_products_sku`  (`sku`),
    KEY `idx_products_category`   (`category_id`),
    KEY `idx_products_collection`  (`collection_id`),
    KEY `idx_products_active_order` (`is_active`, `display_order`),
    KEY `idx_products_featured`   (`is_featured`),

    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`)   REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_products_collection`
        FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_products_created_by`
        FOREIGN KEY (`created_by`)    REFERENCES `admin_users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
    `id`          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `product_id`  INT UNSIGNED      NOT NULL,
    `filename`    VARCHAR(260)      NOT NULL,
    `alt_text`    VARCHAR(200)      NULL,
    `is_cover`    TINYINT(1)        NOT NULL DEFAULT 0,
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_pimages_product` (`product_id`),
    KEY `idx_pimages_cover`   (`product_id`, `is_cover`),

    CONSTRAINT `fk_pimages_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_materials` (
    `product_id`  INT UNSIGNED NOT NULL,
    `material_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `material_id`),
    CONSTRAINT `fk_pm_product`  FOREIGN KEY (`product_id`)  REFERENCES `products`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pm_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_colors` (
    `product_id` INT UNSIGNED NOT NULL,
    `color_id`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `color_id`),
    CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pc_color`   FOREIGN KEY (`color_id`)   REFERENCES `colors`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `collections` (`name`, `slug`, `description`, `display_order`) VALUES
('Heritage',  'heritage',  'Timeless designs drawing from classical architectural traditions.', 1),
('Moderne',   'moderne',   'Clean lines and minimalist forms for contemporary spaces.',         2),
('Prestige',  'prestige',  'Ultra-premium bespoke doors for landmark projects.',                3);

INSERT IGNORE INTO `materials` (`name`, `display_order`) VALUES
('Solid Oak',      1),
('Walnut',         2),
('Steel',          3),
('Aluminium',      4),
('Glass',          5),
('Composite',      6);

INSERT IGNORE INTO `colors` (`name`, `hex`, `display_order`) VALUES
('Anthracite Black', '#2C2C2C', 1),
('Pure White',       '#F8F8F8', 2),
('Brushed Bronze',   '#8C6A3F', 3),
('Natural Oak',      '#C5A35A', 4),
('Dark Walnut',      '#3B2314', 5),
('Raw Steel',        '#7A7A7A', 6);
