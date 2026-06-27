-- ============================================================
--  PORTES — Room / Project type for the configurator
--  A proper lookup table (not an enum) so it is admin-extensible
--  and future pivots can filter collections/products by room type.
-- ============================================================

CREATE TABLE IF NOT EXISTS `room_types` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(80)  NOT NULL,
  `slug`          VARCHAR(100) NOT NULL,
  `description`   VARCHAR(255) DEFAULT NULL,
  `display_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_room_slug` (`slug`),
  KEY `idx_room_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `room_types` (`name`, `slug`, `display_order`) VALUES
('Bedroom Door',     'bedroom',     1),
('Bathroom Door',    'bathroom',    2),
('Kitchen Door',     'kitchen',     3),
('Living Room Door', 'living-room', 4),
('Office Door',      'office',      5),
('Entrance Door',    'entrance',    6),
('Commercial Door',  'commercial',  7),
('Other',            'other',       8);

-- room_type_id on quote_requests (additive, re-runnable)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='quote_requests' AND COLUMN_NAME='room_type_id');
SET @s := IF(@c=0,
  'ALTER TABLE `quote_requests` ADD COLUMN `room_type_id` INT UNSIGNED NULL AFTER `door_type_id`,
   ADD KEY `idx_qr_room` (`room_type_id`),
   ADD CONSTRAINT `fk_qr_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Forward-looking pivot so collections can later be filtered by room type.
-- (Empty for now — no fake data. The structure exists for future admin use.)
CREATE TABLE IF NOT EXISTS `collection_room_types` (
  `collection_id` INT UNSIGNED NOT NULL,
  `room_type_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`collection_id`, `room_type_id`),
  KEY `idx_crt_room` (`room_type_id`),
  CONSTRAINT `fk_crt_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crt_room`       FOREIGN KEY (`room_type_id`)  REFERENCES `room_types`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
