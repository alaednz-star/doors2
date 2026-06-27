-- ============================================================
--  PORTES — Phase 9: final real-catalog reconciliation.
--  Applies the client's authoritative pricing, removes demo
--  categories, and seeds the one confirmed product (Le Chêne).
--  Idempotent. Le Chêne inherits Heritage colors + matrix
--  (no per-product colors, no hardcoded prices).
-- ============================================================

START TRANSACTION;

-- 1. Pricing deltas vs the client's authoritative matrix.
UPDATE price_rules r
JOIN collections c        ON c.id  = r.collection_id
JOIN door_types d         ON d.id  = r.door_type_id
JOIN construction_types t ON t.id  = r.construction_type_id
SET r.base_price = 40000, r.is_available = 1
WHERE c.slug='moderne' AND d.slug='chambre' AND t.slug='tebelaire';

UPDATE price_rules r
JOIN collections c        ON c.id  = r.collection_id
JOIN door_types d         ON d.id  = r.door_type_id
JOIN construction_types t ON t.id  = r.construction_type_id
SET r.base_price = 58000, r.is_available = 1
WHERE c.slug='prestige' AND d.slug='salon' AND t.slug='nedabaile';

-- Prestige · Porte d'Entrée · Nédabaile is now UNAVAILABLE (client update).
UPDATE price_rules r
JOIN collections c        ON c.id  = r.collection_id
JOIN door_types d         ON d.id  = r.door_type_id
JOIN construction_types t ON t.id  = r.construction_type_id
SET r.base_price = 0, r.is_available = 0
WHERE c.slug='prestige' AND d.slug='porte-entree' AND t.slug='nedabaile';

-- 2. Remove demo categories (template data; model uses Collections + Usages).
UPDATE products SET category_id = NULL WHERE category_id IS NOT NULL;
DELETE FROM categories;
ALTER TABLE categories AUTO_INCREMENT = 1;

-- 3. Seed the one confirmed real product: Le Chêne (Heritage).
--    Colors inherit from the collection — no product_colors rows.
--    Price comes from the Heritage matrix — no per-product price rules.
DELETE FROM product_colors    WHERE product_id IN (SELECT id FROM products WHERE slug='le-chene');
DELETE FROM product_materials WHERE product_id IN (SELECT id FROM products WHERE slug='le-chene');
DELETE FROM products WHERE slug='le-chene';

INSERT INTO products (name, slug, sku, description, width_mm, height_mm,
                      display_order, collection_id, construction_type_id, is_featured, is_active)
SELECT 'Le Chêne', 'le-chene', NULL,
       'A Heritage door in solid character oak, made to your exact opening.',
       900, 2100, 1, c.id, NULL, 1, 1
FROM collections c WHERE c.slug='heritage' LIMIT 1;

COMMIT;
