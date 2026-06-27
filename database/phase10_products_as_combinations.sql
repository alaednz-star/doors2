-- ============================================================
--  PORTES — Phase 10: Products ARE sellable combinations.
--  A product = Collection × Color × Door Usage × Construction + price.
--  Generated from the AVAILABLE pricing-matrix cells × each
--  collection's colors. Matrix stays the source of truth.
--  Idempotent.
-- ============================================================

START TRANSACTION;

-- 1. Per-product attribute columns (collection_id + construction_type_id already exist).
SET @db := DATABASE();

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='color_id');
SET @s := IF(@x=0,'ALTER TABLE products ADD COLUMN color_id INT UNSIGNED NULL AFTER collection_id','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='door_type_id');
SET @s := IF(@x=0,'ALTER TABLE products ADD COLUMN door_type_id INT UNSIGNED NULL AFTER color_id','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='products' AND COLUMN_NAME='base_price');
SET @s := IF(@x=0,'ALTER TABLE products ADD COLUMN base_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER door_type_id','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- FKs for the new attribute columns (only if absent).
SET @x := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='products' AND CONSTRAINT_NAME='fk_products_color');
SET @s := IF(@x=0,'ALTER TABLE products ADD CONSTRAINT fk_products_color FOREIGN KEY (color_id) REFERENCES colors(id) ON DELETE SET NULL','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='products' AND CONSTRAINT_NAME='fk_products_doortype');
SET @s := IF(@x=0,'ALTER TABLE products ADD CONSTRAINT fk_products_doortype FOREIGN KEY (door_type_id) REFERENCES door_types(id) ON DELETE SET NULL','SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Reconcile the matrix to the client's paper:
--    Prestige · Porte d'Entrée · Tebelaire is AVAILABLE (price set later by admin).
UPDATE price_rules r
JOIN collections c        ON c.id = r.collection_id
JOIN door_types d         ON d.id = r.door_type_id
JOIN construction_types t ON t.id = r.construction_type_id
SET r.is_available = 1, r.base_price = 0
WHERE c.slug='prestige' AND d.slug='porte-entree' AND t.slug='tebelaire';

-- 3. Remove every demo/old product and its links.
DELETE FROM product_colors;
DELETE FROM product_materials;
DELETE FROM product_images;
DELETE FROM products;
ALTER TABLE products AUTO_INCREMENT = 1;

-- 4. Generate one product per (available matrix cell × each color of that collection).
--    slug = collection-color-usage-construction (lowercase, hyphenated).
INSERT INTO products
  (name, slug, base_price, collection_id, color_id, door_type_id, construction_type_id,
   width_mm, height_mm, display_order, is_active, is_featured)
SELECT
  CONCAT(col.name, ' ', clr.name, ' · ', dt.name, ' · ', ct.name) AS name,
  LOWER(CONCAT(
     col.slug, '-',
     REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(clr.name),' ','-'),'é','e'),'è','e'),'ê','e'),'ç','c'), '-',
     dt.slug, '-', ct.slug
  )) AS slug,
  r.base_price,
  col.id, clr.id, dt.id, ct.id,
  900, 2100, 0, 1, 0
FROM price_rules r
JOIN collections        col ON col.id = r.collection_id
JOIN door_types         dt  ON dt.id  = r.door_type_id
JOIN construction_types ct  ON ct.id  = r.construction_type_id
JOIN colors             clr ON clr.collection_id = col.id AND clr.is_active = 1
WHERE r.is_active = 1 AND r.is_available = 1
ORDER BY col.display_order, clr.display_order, dt.display_order, ct.display_order;

COMMIT;
