-- ============================================================
--  PORTES — Phase 7: correct the Color data model.
--  A color name is unique WITHIN a collection, not globally.
--  Rename the Prestige colors to drop the redundant collection
--  suffix. FKs/relationships are preserved (ids never change).
-- ============================================================

START TRANSACTION;

-- 1. Replace the global-name unique key with a per-collection one,
--    so "Gris" can exist in both Heritage and Prestige.
SET @hasOld := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'colors' AND INDEX_NAME = 'uk_colors_name');
SET @s := IF(@hasOld > 0, 'ALTER TABLE `colors` DROP INDEX `uk_colors_name`', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @hasNew := (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'colors' AND INDEX_NAME = 'uk_colors_collection_name');
SET @s := IF(@hasNew = 0,
  'ALTER TABLE `colors` ADD UNIQUE KEY `uk_colors_collection_name` (`collection_id`, `name`)',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Rename the Prestige colors (id-targeted; FKs untouched).
UPDATE `colors` SET `name` = 'Marron' WHERE `name` = 'Marron Prestige';
UPDATE `colors` SET `name` = 'Gris'   WHERE `name` = 'Gris Prestige';

COMMIT;
