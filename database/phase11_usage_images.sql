-- ============================================================
--  PORTES — Phase 11: add an uploadable image to Door Usages.
--  door_types (usages) gains an image_filename column so the
--  admin can upload a photo per usage and the configurator can
--  show it on the Usage step. Idempotent.
-- ============================================================

START TRANSACTION;

SET @db := DATABASE();

-- Add door_types.image_filename if it does not already exist.
SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='door_types' AND COLUMN_NAME='image_filename');
SET @s := IF(@x=0,
  'ALTER TABLE `door_types` ADD COLUMN `image_filename` VARCHAR(260) DEFAULT NULL AFTER `description`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

COMMIT;
