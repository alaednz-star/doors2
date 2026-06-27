-- ============================================================
--  PORTES — Phase 6: purge demo data, seed the REAL client catalog
--  Collections/Colors/Usages/Constructions already match the client and
--  are preserved. Products and the pricing matrix are rebuilt from the
--  client's real numbers. Everything stays admin-editable.
-- ============================================================

START TRANSACTION;

-- ── 1. Remove ALL demo products (client adds real products via admin) ──
DELETE FROM product_colors;
DELETE FROM product_materials;
DELETE FROM product_finishes;
DELETE FROM product_images;
DELETE FROM products;
ALTER TABLE products AUTO_INCREMENT = 1;

-- ── 2. Remove demo quotes / saved configs / calc logs (testing only) ──
DELETE FROM quote_status_log;
DELETE FROM quote_requests;
DELETE FROM saved_configurations;
DELETE FROM price_calculations;

-- ── 3. Colors: keep the real catalog; ensure Scuro (Moderne) is active ──
UPDATE colors SET is_active = 1 WHERE name = 'Scuro';

-- ── 4. Pricing matrix: wipe demo prices, load the client's real matrix ──
DELETE FROM price_rules;
ALTER TABLE price_rules AUTO_INCREMENT = 1;

INSERT INTO price_rules
  (name, dimension_type, collection_id, door_type_id, construction_type_id,
   base_price, priority, is_active, is_available)
SELECT
  CONCAT(col.name, ' · ', dt.name, ' · ', ct.name),
  'reference_scaled', col.id, dt.id, ct.id,
  m.price, 100, 1, m.available
FROM (
  -- ─ PRESTIGE ─ (all Tebelaire = Not Available)
  SELECT 'prestige' c, 'chambre'      u, 'nedabaile' k, 44000 price, 1 available UNION ALL
  SELECT 'prestige', 'chambre',        'tebelaire', 0,     0 UNION ALL
  SELECT 'prestige', 'sanitaire',      'nedabaile', 41500, 1 UNION ALL
  SELECT 'prestige', 'sanitaire',      'tebelaire', 0,     0 UNION ALL
  SELECT 'prestige', 'salon',          'nedabaile', 59000, 1 UNION ALL
  SELECT 'prestige', 'salon',          'tebelaire', 0,     0 UNION ALL
  -- Porte d'Entrée + Nédabaile: available, price not yet provided (admin sets later)
  SELECT 'prestige', 'porte-entree',   'nedabaile', 0,     1 UNION ALL
  SELECT 'prestige', 'porte-entree',   'tebelaire', 0,     0 UNION ALL

  -- ─ MODERNE ─
  SELECT 'moderne',  'chambre',        'nedabaile', 34000, 1 UNION ALL
  SELECT 'moderne',  'chambre',        'tebelaire', 41000, 1 UNION ALL
  SELECT 'moderne',  'sanitaire',      'nedabaile', 33500, 1 UNION ALL
  SELECT 'moderne',  'sanitaire',      'tebelaire', 42500, 1 UNION ALL
  SELECT 'moderne',  'salon',          'nedabaile', 57000, 1 UNION ALL
  SELECT 'moderne',  'salon',          'tebelaire', 60000, 1 UNION ALL
  SELECT 'moderne',  'porte-entree',   'nedabaile', 0,     0 UNION ALL
  SELECT 'moderne',  'porte-entree',   'tebelaire', 56000, 1 UNION ALL

  -- ─ HERITAGE ─
  SELECT 'heritage', 'chambre',        'nedabaile', 34000, 1 UNION ALL
  SELECT 'heritage', 'chambre',        'tebelaire', 38000, 1 UNION ALL
  SELECT 'heritage', 'sanitaire',      'nedabaile', 35500, 1 UNION ALL
  SELECT 'heritage', 'sanitaire',      'tebelaire', 39500, 1 UNION ALL
  SELECT 'heritage', 'salon',          'nedabaile', 54000, 1 UNION ALL
  SELECT 'heritage', 'salon',          'tebelaire', 58000, 1 UNION ALL
  SELECT 'heritage', 'porte-entree',   'nedabaile', 0,     0 UNION ALL
  SELECT 'heritage', 'porte-entree',   'tebelaire', 54000, 1
) m
JOIN collections        col ON col.slug = m.c
JOIN door_types         dt  ON dt.slug  = m.u
JOIN construction_types ct  ON ct.slug  = m.k;

-- NOTE: PVC intentionally has NO price_rules — it exists as a construction type
-- only; the admin can add PVC pricing later. No PVC rows are seeded.

COMMIT;
