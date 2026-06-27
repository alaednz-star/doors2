USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `categories` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120)      NOT NULL,
    `slug`          VARCHAR(140)      NOT NULL,
    `description`   TEXT              NULL,
    `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)        NOT NULL DEFAULT 1,
    `created_by`    INT UNSIGNED      NULL,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categories_slug` (`slug`),
    KEY `idx_categories_active_order` (`is_active`, `display_order`),
    KEY `idx_categories_created_by` (`created_by`),

    CONSTRAINT `fk_categories_created_by`
        FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `display_order`, `is_active`, `created_by`) VALUES
('Exterior Doors',  'exterior-doors',  'Architectural entrance doors for residential and commercial facades.',    1, 1, 1),
('Interior Doors',  'interior-doors',  'Internal doors combining acoustic performance with refined aesthetics.',   2, 1, 1),
('Pivot Doors',     'pivot-doors',     'Statement pivot doors rotating on a central or offset axis.',              3, 1, 1),
('Sliding Doors',   'sliding-doors',   'Space-efficient sliding systems for contemporary interiors.',              4, 1, 1);
