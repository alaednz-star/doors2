-- ============================================================
--  PORTES — Phase 1: schema migration for the real pricing model
--  Collection × Door Usage × Construction Type → Price + Availability
--  Additive & re-runnable. No destructive drops (finishes/materials/
--  room_types kept dormant for FK safety).
-- ============================================================

-- ── construction_types (new lookup: Nédabaile / Tebelaire / PVC) ──
CREATE TABLE IF NOT EXISTS `construction_types` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(100) NOT NULL,
  `slug`           VARCHAR(120) NOT NULL,
  `description`    TEXT DEFAULT NULL,
  `image_filename` VARCHAR(260) DEFAULT NULL,
  `display_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ct_slug` (`slug`),
  KEY `idx_ct_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── price_rules: + construction_type_id, + is_available ──
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='price_rules' AND COLUMN_NAME='construction_type_id');
SET @s := IF(@c=0,
  'ALTER TABLE `price_rules` ADD COLUMN `construction_type_id` INT UNSIGNED NULL AFTER `door_type_id`,
   ADD KEY `idx_pr_construction` (`construction_type_id`)',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='price_rules' AND COLUMN_NAME='is_available');
SET @s := IF(@c=0,
  'ALTER TABLE `price_rules` ADD COLUMN `is_available` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_active`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- FK for construction_type_id (only if absent)
SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='price_rules' AND CONSTRAINT_NAME='fk_pr_construction');
SET @s := IF(@c=0,
  'ALTER TABLE `price_rules` ADD CONSTRAINT `fk_pr_construction` FOREIGN KEY (`construction_type_id`) REFERENCES `construction_types`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ── price_rules.dimension_type: allow reference-scaled pricing ──
ALTER TABLE `price_rules`
  MODIFY COLUMN `dimension_type` ENUM('fixed','per_sqm','per_lm','reference_scaled')
  NOT NULL DEFAULT 'reference_scaled';

-- ── colors: + collection_id (belongs-to), + texture_filename ──
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='colors' AND COLUMN_NAME='collection_id');
SET @s := IF(@c=0,
  'ALTER TABLE `colors` ADD COLUMN `collection_id` INT UNSIGNED NULL AFTER `id`,
   ADD KEY `idx_colors_collection` (`collection_id`),
   ADD CONSTRAINT `fk_colors_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='colors' AND COLUMN_NAME='texture_filename');
SET @s := IF(@c=0,
  'ALTER TABLE `colors` ADD COLUMN `texture_filename` VARCHAR(260) DEFAULT NULL AFTER `image_filename`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ── collections: + image_filename, + hero_image ──
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='collections' AND COLUMN_NAME='image_filename');
SET @s := IF(@c=0,
  'ALTER TABLE `collections` ADD COLUMN `image_filename` VARCHAR(260) DEFAULT NULL AFTER `description`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='collections' AND COLUMN_NAME='hero_image');
SET @s := IF(@c=0,
  'ALTER TABLE `collections` ADD COLUMN `hero_image` VARCHAR(260) DEFAULT NULL AFTER `image_filename`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
