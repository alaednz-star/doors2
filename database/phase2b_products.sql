-- Phase 2b: product dimensions as numeric W x H + single construction type
-- Additive and re-runnable.

SET @db := DATABASE();

-- width_mm
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='width_mm');
SET @s := IF(@c=0,
  'ALTER TABLE products ADD COLUMN width_mm INT UNSIGNED NULL AFTER dimensions',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- height_mm
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='height_mm');
SET @s := IF(@c=0,
  'ALTER TABLE products ADD COLUMN height_mm INT UNSIGNED NULL AFTER width_mm',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- construction_type_id (single FK)
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='construction_type_id');
SET @s := IF(@c=0,
  'ALTER TABLE products ADD COLUMN construction_type_id INT UNSIGNED NULL AFTER collection_id',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- FK for construction_type_id
SET @fk := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND CONSTRAINT_NAME='fk_products_construction');
SET @s := IF(@fk=0,
  'ALTER TABLE products ADD CONSTRAINT fk_products_construction FOREIGN KEY (construction_type_id) REFERENCES construction_types(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Backfill width/height from legacy free-text dimensions where it matches a "NNN x NNN" shape.
UPDATE products
SET width_mm  = CAST(REGEXP_REPLACE(dimensions, '^[^0-9]*([0-9]+).*$', '\\1') AS UNSIGNED),
    height_mm = CAST(REGEXP_REPLACE(dimensions, '^[^0-9]*[0-9]+[^0-9]+([0-9]+).*$', '\\1') AS UNSIGNED)
WHERE (width_mm IS NULL OR height_mm IS NULL)
  AND dimensions REGEXP '[0-9]+[^0-9]+[0-9]+';
