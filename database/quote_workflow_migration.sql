-- ============================================================
--  PORTES — Quote workflow migration (additive, preserves data)
--  Adds the lead-capture fields the customer quote journey needs.
--  Safe to re-run: each column is added only if absent.
-- ============================================================

-- customer_company
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'customer_company');
SET @s := IF(@c = 0,
  'ALTER TABLE `quote_requests` ADD COLUMN `customer_company` VARCHAR(160) NULL AFTER `customer_email`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- customer_country
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'customer_country');
SET @s := IF(@c = 0,
  'ALTER TABLE `quote_requests` ADD COLUMN `customer_country` VARCHAR(100) NULL AFTER `customer_company`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- project_type
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'project_type');
SET @s := IF(@c = 0,
  "ALTER TABLE `quote_requests` ADD COLUMN `project_type` ENUM('residential','commercial','hospitality','architectural') NULL AFTER `customer_city`",
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- install_date (desired installation date)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'install_date');
SET @s := IF(@c = 0,
  'ALTER TABLE `quote_requests` ADD COLUMN `install_date` DATE NULL AFTER `project_type`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- quantity (number of doors)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'quantity');
SET @s := IF(@c = 0,
  'ALTER TABLE `quote_requests` ADD COLUMN `quantity` SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER `install_date`',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- collection_id (the customer journey starts at collection, store it directly)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quote_requests' AND COLUMN_NAME = 'collection_id');
SET @s := IF(@c = 0,
  'ALTER TABLE `quote_requests` ADD COLUMN `collection_id` INT UNSIGNED NULL AFTER `product_id`, ADD KEY `idx_qr_collection` (`collection_id`)',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
