USE `door_showroom`;

ALTER TABLE `colors`
    ADD COLUMN IF NOT EXISTS `description`    TEXT              NULL         AFTER `hex`,
    ADD COLUMN IF NOT EXISTS `image_filename` VARCHAR(260)      NULL         AFTER `description`,
    ADD COLUMN IF NOT EXISTS `updated_at`     DATETIME          NOT NULL
                             DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP         AFTER `created_at`;

ALTER TABLE `finishes`
    ADD COLUMN IF NOT EXISTS `description`    TEXT              NULL         AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `image_filename` VARCHAR(260)      NULL         AFTER `description`,
    ADD COLUMN IF NOT EXISTS `updated_at`     DATETIME          NOT NULL
                             DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP         AFTER `created_at`;

CREATE TABLE IF NOT EXISTS `product_finishes` (
    `product_id` INT UNSIGNED NOT NULL,
    `finish_id`  INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `finish_id`),
    CONSTRAINT `fk_pf_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pf_finish`  FOREIGN KEY (`finish_id`)  REFERENCES `finishes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
