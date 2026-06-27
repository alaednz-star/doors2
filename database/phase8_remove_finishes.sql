-- ============================================================
--  PORTES — Phase 8: remove the Finishes feature entirely.
--  Finishes were demo/template data and are not part of the
--  client's business. Drop FKs, finish_id columns, and the
--  finishes / product_finishes tables. Idempotent.
-- ============================================================

START TRANSACTION;

-- Helper macro pattern: drop a FK only if it exists.
SET @db := DATABASE();

-- 1. Drop foreign keys that reference the finishes table.
SET @x := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='price_rules' AND CONSTRAINT_NAME='fk_pr_finish');
SET @s := IF(@x>0,'ALTER TABLE `price_rules` DROP FOREIGN KEY `fk_pr_finish`','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='quote_requests' AND CONSTRAINT_NAME='fk_qr_finish');
SET @s := IF(@x>0,'ALTER TABLE `quote_requests` DROP FOREIGN KEY `fk_qr_finish`','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Drop the product_finishes pivot entirely (its FK goes with it).
DROP TABLE IF EXISTS `product_finishes`;

-- 3. Drop finish_id columns (with their indexes) where present.
SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='price_rules' AND COLUMN_NAME='finish_id');
SET @s := IF(@x>0,'ALTER TABLE `price_rules` DROP COLUMN `finish_id`','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='quote_requests' AND COLUMN_NAME='finish_id');
SET @s := IF(@x>0,'ALTER TABLE `quote_requests` DROP COLUMN `finish_id`','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='price_calculations' AND COLUMN_NAME='finish_id');
SET @s := IF(@x>0,'ALTER TABLE `price_calculations` DROP COLUMN `finish_id`','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 4. Drop the finishes table and its demo seed data.
DROP TABLE IF EXISTS `finishes`;

-- 5. Trim the media entity_type enum to remove 'finish'.
ALTER TABLE `media`
  MODIFY COLUMN `entity_type` ENUM('product','collection','color') DEFAULT NULL;

COMMIT;
