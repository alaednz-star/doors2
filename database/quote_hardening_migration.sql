-- Quote hardening: config_hash for dedup + FK on collection_id (audit fix). Re-runnable.

-- config_hash
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='quote_requests' AND COLUMN_NAME='config_hash');
SET @s := IF(@c=0,
  'ALTER TABLE `quote_requests` ADD COLUMN `config_hash` CHAR(40) NULL AFTER `features_json`, ADD KEY `idx_qr_dedup` (`customer_email`,`config_hash`,`submitted_at`)',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- FK on collection_id (only if missing)
SET @c := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='quote_requests' AND CONSTRAINT_NAME='fk_qr_collection');
SET @s := IF(@c=0,
  'ALTER TABLE `quote_requests` ADD CONSTRAINT `fk_qr_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections`(`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
