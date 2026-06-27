-- ============================================================
--  PORTES — Configurator pricing seed (DZD)
--  All values live in price_rules; the calculator sums every
--  matching rule's contribution. Re-runnable (clears first).
--
--  Model:
--    • Collection base  → per_sqm rule keyed by collection_id
--    • Material          → fixed surcharge keyed by material_id
--    • Finish            → fixed surcharge keyed by finish_id
--    • Colour            → fixed surcharge keyed by color_id
--    • Door type         → fixed surcharge keyed by door_type_id
--    • Width/height      → handled by the per_sqm base (scales with area)
--    • Optional features → optional_features table (already seeded)
-- ============================================================

DELETE FROM `price_rules`;
ALTER TABLE `price_rules` AUTO_INCREMENT = 1;

-- ── COLLECTION BASE (per square metre, scales with dimensions) ──
-- A standard 900×2100 door = 1.89 sqm.
INSERT INTO `price_rules`
  (`name`, `dimension_type`, `collection_id`, `base_price`, `price_modifier`, `multiplier`, `priority`, `is_active`)
SELECT CONCAT(c.name, ' — base (per m²)'), 'per_sqm', c.id, v.base, 0, 1.0000, 100, 1
FROM `collections` c
JOIN (
  SELECT 'heritage' slug, 32000 base UNION ALL
  SELECT 'moderne',  28000          UNION ALL
  SELECT 'prestige', 46000
) v ON v.slug = c.slug;

-- ── MATERIAL surcharge (fixed, DZD) ──
INSERT INTO `price_rules`
  (`name`, `dimension_type`, `material_id`, `base_price`, `price_modifier`, `multiplier`, `priority`, `is_active`)
SELECT CONCAT('Material — ', m.name), 'fixed', m.id, v.surcharge, 0, 1.0000, 80, 1
FROM `materials` m
JOIN (
  SELECT 'Solid Oak' name, 18000 surcharge UNION ALL
  SELECT 'Walnut',          24000          UNION ALL
  SELECT 'Steel',           15000          UNION ALL
  SELECT 'Aluminium',       12000          UNION ALL
  SELECT 'Glass',           21000          UNION ALL
  SELECT 'Composite',        8000
) v ON v.name = m.name;

-- ── FINISH surcharge (fixed, DZD) ──
INSERT INTO `price_rules`
  (`name`, `dimension_type`, `finish_id`, `base_price`, `price_modifier`, `multiplier`, `priority`, `is_active`)
SELECT CONCAT('Finish — ', f.name), 'fixed', f.id, v.surcharge, 0, 1.0000, 60, 1
FROM `finishes` f
JOIN (
  SELECT 'Matte' name, 0 surcharge UNION ALL
  SELECT 'Gloss',       6500       UNION ALL
  SELECT 'Satin',       3500       UNION ALL
  SELECT 'Brushed',     4500       UNION ALL
  SELECT 'Lacquered',   9000       UNION ALL
  SELECT 'Natural Oiled', 5000
) v ON v.name = f.name;

-- ── COLOUR surcharge (fixed, DZD) ──
INSERT INTO `price_rules`
  (`name`, `dimension_type`, `color_id`, `base_price`, `price_modifier`, `multiplier`, `priority`, `is_active`)
SELECT CONCAT('Colour — ', co.name), 'fixed', co.id, v.surcharge, 0, 1.0000, 40, 1
FROM `colors` co
JOIN (
  SELECT 'Anthracite Black' name, 2000 surcharge UNION ALL
  SELECT 'Pure White',            0              UNION ALL
  SELECT 'Brushed Bronze',        8500           UNION ALL
  SELECT 'Natural Oak',           2500           UNION ALL
  SELECT 'Dark Walnut',           4000           UNION ALL
  SELECT 'Raw Steel',             3000
) v ON v.name = co.name;

-- ── DOOR TYPE surcharge (fixed, DZD) ──
INSERT INTO `price_rules`
  (`name`, `dimension_type`, `door_type_id`, `base_price`, `price_modifier`, `multiplier`, `priority`, `is_active`)
SELECT CONCAT('Opening — ', d.name), 'fixed', d.id, v.surcharge, 0, 1.0000, 20, 1
FROM `door_types` d
JOIN (
  SELECT 'Interior' name, 0 surcharge UNION ALL
  SELECT 'Exterior',      9000        UNION ALL
  SELECT 'Sliding',       14000       UNION ALL
  SELECT 'Pivot',         22000       UNION ALL
  SELECT 'Bi-Fold',       18000       UNION ALL
  SELECT 'French Double', 26000
) v ON v.name = d.name;
