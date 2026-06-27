-- ============================================================
--  PORTES — Phase 1 seed (DEV values; all admin-editable later)
--  Reseeds usages + construction types + colors→collections,
--  pricing reference size, and the Collection×Usage×Construction matrix.
--  Idempotent where practical.
-- ============================================================

-- ── DOOR USAGES (repurpose door_types) ──
-- Safe: no quote_requests reference door_type_id (verified).
DELETE FROM `door_types`;
ALTER TABLE `door_types` AUTO_INCREMENT = 1;
INSERT INTO `door_types` (`name`, `slug`, `display_order`, `is_active`) VALUES
('Chambre',         'chambre',       1, 1),
('Sanitaire',       'sanitaire',     2, 1),
('Salon',           'salon',         3, 1),
('Porte d''Entrée', 'porte-entree',  4, 1);

-- ── CONSTRUCTION TYPES ──
-- PVC intentionally omitted — add it via the admin panel if/when needed.
INSERT INTO `construction_types` (`name`, `slug`, `display_order`, `is_active`) VALUES
('Nédabaile', 'nedabaile', 1, 1),
('Tebelaire', 'tebelaire', 2, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `display_order` = VALUES(`display_order`);

-- ── COLORS reseeded to the real catalog, linked to collections ──
-- Safe: no quote_requests reference color_id (verified).
DELETE FROM `colors`;
ALTER TABLE `colors` AUTO_INCREMENT = 1;
INSERT INTO `colors` (`collection_id`, `name`, `hex`, `display_order`, `is_active`)
SELECT c.id, v.name, v.hex, v.ord, 1 FROM (
  -- Prestige
  SELECT 'prestige' slug, 'Marron Prestige' name, '#5A3A24' hex, 1 ord UNION ALL
  SELECT 'prestige', 'Gris Prestige',   '#6E6E6E', 2 UNION ALL
  -- Moderne
  SELECT 'moderne',  'Scuro',           '#2E2622', 1 UNION ALL
  SELECT 'moderne',  'Simza',           '#9A9389', 2 UNION ALL
  SELECT 'moderne',  'Madera',          '#7A4E2D', 3 UNION ALL
  SELECT 'moderne',  'Wengue',          '#3B2314', 4 UNION ALL
  SELECT 'moderne',  'Serya',           '#C9B79C', 5 UNION ALL
  -- Heritage
  SELECT 'heritage', 'Chêne',           '#B98E54', 1 UNION ALL
  SELECT 'heritage', 'Gris',            '#8A8A8A', 2
) v
JOIN `collections` c ON c.slug = v.slug;

-- ── PRICING REFERENCE SIZE (settings, admin-editable) ──
INSERT INTO `settings` (`setting_key`, `setting_value`, `group_name`)
VALUES ('pricing_ref_width_mm', '900', 'pricing'), ('pricing_ref_height_mm', '2100', 'pricing')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ── PRICE MATRIX: Collection × Usage × Construction → base_price + availability ──
-- Clear the demo additive rules, then load the dev matrix.
DELETE FROM `price_rules`;
ALTER TABLE `price_rules` AUTO_INCREMENT = 1;

INSERT INTO `price_rules`
  (`name`, `dimension_type`, `collection_id`, `door_type_id`, `construction_type_id`,
   `base_price`, `priority`, `is_active`, `is_available`)
SELECT
  CONCAT(col.name, ' · ', dt.name, ' · ', ct.name),
  'reference_scaled', col.id, dt.id, ct.id,
  m.price, 100, 1, m.available
FROM (
  -- Prestige
  SELECT 'prestige' c, 'chambre'      u, 'nedabaile' k, 44000 price, 1 available UNION ALL
  SELECT 'prestige', 'chambre',        'tebelaire', 49000, 1 UNION ALL
  SELECT 'prestige', 'sanitaire',      'nedabaile', 46000, 1 UNION ALL
  SELECT 'prestige', 'sanitaire',      'tebelaire', 51000, 1 UNION ALL
  SELECT 'prestige', 'porte-entree',   'nedabaile', 0,     0 UNION ALL
  SELECT 'prestige', 'porte-entree',   'tebelaire', 65000, 1 UNION ALL
  -- Moderne
  SELECT 'moderne',  'chambre',        'nedabaile', 34000, 1 UNION ALL
  SELECT 'moderne',  'chambre',        'tebelaire', 39000, 1 UNION ALL
  SELECT 'moderne',  'sanitaire',      'nedabaile', 36000, 1 UNION ALL
  SELECT 'moderne',  'sanitaire',      'tebelaire', 41000, 1 UNION ALL
  SELECT 'moderne',  'porte-entree',   'nedabaile', 0,     0 UNION ALL
  SELECT 'moderne',  'porte-entree',   'tebelaire', 54000, 1 UNION ALL
  -- Heritage
  SELECT 'heritage', 'chambre',        'nedabaile', 38000, 1 UNION ALL
  SELECT 'heritage', 'chambre',        'tebelaire', 43000, 1 UNION ALL
  SELECT 'heritage', 'sanitaire',      'nedabaile', 40000, 1 UNION ALL
  SELECT 'heritage', 'sanitaire',      'tebelaire', 45000, 1 UNION ALL
  SELECT 'heritage', 'porte-entree',   'nedabaile', 0,     0 UNION ALL
  SELECT 'heritage', 'porte-entree',   'tebelaire', 58000, 1
) m
JOIN `collections`        col ON col.slug = m.c
JOIN `door_types`         dt  ON dt.slug  = m.u
JOIN `construction_types` ct  ON ct.slug  = m.k;
