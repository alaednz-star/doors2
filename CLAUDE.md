# PORTES — Architectural Door Showroom & Configurator

Luxury door e-commerce/configurator for an Algerian client (brand **PORTES**),
prices in **DZD**. Customers browse collections, configure a door (colour-first),
and request a quote. Admins manage the full catalogue + pricing.

> **UNRELATED PROJECT WARNING:** A separate digital-signage app lives at
> `/opt/lampp/htdocs/sdms`. It shares nothing with this project — never touch or
> reference it while working here.

## Stack & how to run
- **PHP 8.3**, no framework. Custom front controllers + a PSR-4-style autoloader
  (`App\` → `src/`). No Composer/vendor.
- **MySQL 8 / MariaDB** via LAMPP. CLI tools: `/opt/lampp/bin/php`, `/opt/lampp/bin/mysql`.
  Database name: `door_showroom`. Connection config in `config/database.php`.
- **Vanilla JS** (no build step). Per-page CSS/JS in `public/assets/`.
- Served by the **system Apache (`www-data`)** on port 80 at path `/door-showroom`.
  LAMPP's own Apache is NOT used. Upload dirs under `public/uploads/` must be
  writable by `www-data` (currently `0777`).
- Start MySQL if down: `sudo /opt/lampp/lampp startmysql` (data dir owned by `mysql`).

Public site: `http://localhost/door-showroom`
Admin: `http://localhost/door-showroom/admin` (seed login `admin@showroom.dz`).

## Architecture
- Front controllers: `public/index.php` (public), `public/admin.php` (admin).
  Each holds a static route map + a few regex parametric routes, then dispatches
  `(new $Controller())->$action()`.
- `src/Controllers/` (public) and `src/Controllers/Admin/` (admin CRUD).
- Plain-PHP views in `src/Views/`. Services in `src/Services/`, validators in
  `src/Validators/`, auth in `src/Auth/`, middleware in `src/Middleware/`,
  PDO/Config/Session/Logger in `src/Core/`.
- DB schema/migrations/seeds are raw SQL in `database/` — applied manually with
  `/opt/lampp/bin/mysql door_showroom < database/<file>.sql`. No migration runner;
  every phase file is idempotent (information_schema guards + PREPARE/EXECUTE).

## Business model (IMPORTANT — this is the real client model)
The catalogue is built from **Collections, Colours, Products, Door Usages,
Construction Types, a Pricing Matrix, Quotes, and Settings.** There is **no
"Finish" and no "Material" concept** — those were demo/template features and have
been fully removed.

- **Collections** (Heritage, Moderne, Prestige) — real catalogue lines.
- **Colours** belong to exactly ONE collection (`colors.collection_id`). A colour
  name is unique *within* a collection, not globally — e.g. "Gris" exists in both
  Heritage and Prestige (unique key `uk_colors_collection_name`).
  - Prestige → Marron, Gris · Moderne → Scuro, Simza, Madera, Wengue, Serya ·
    Heritage → Chêne, Gris.
- **Door Usages** (`door_types` table): Chambre, Sanitaire, Salon, Porte d'Entrée.
- **Construction Types**: Nédabaile, Tebelaire. (PVC was removed from the
  catalogue; re-add it via the admin panel if/when the client needs it.)
- **Pricing Matrix** (`price_rules`): one row per
  `Collection × Usage × Construction` → `base_price` + `is_available`.
  Source of truth for price + availability. Admin-editable. No hardcoded prices.
- **Products = sellable combinations.** A product IS one available
  `Collection × Colour × Usage × Construction` + price (cols on `products`:
  `collection_id, color_id, door_type_id, construction_type_id, base_price`).
  Products are generated from the *available* matrix cells × each collection's
  colours (57 products). Unavailable combinations are NOT products.
  Product name pattern: `Collection Colour · Usage · Construction`.

## Pricing engine (`src/Services/PricingCalculator.php`)
Matrix lookup + proportional area scaling:
```
cell = price_rules WHERE collection_id, door_type_id, construction_type_id, is_active=1
if !cell or is_available=0  → { available:false, label:"Non disponible" }
refArea = pricing_ref_width_mm × pricing_ref_height_mm   (settings, default 900×2100)
final   = round(cell.base_price × (width×height) / refArea, 2)
```
Returns `available`, `total_price`, `total_price_fmt`, `currency`, etc.
`calculate(array $input): array` is the public API — keep it stable (the
configurator consumes `total_price_fmt`, `total_price`, `currency`).

## Configurator (colour-first)
`/door-showroom/configure` — view `src/Views/configurator2.php`,
JS `public/assets/js/configurator2.js`, backend `ConfiguratorController`.
Flow: **Colour → Collection → Usage → Construction → Dimensions → Review → Quote.**
- Pick a colour → shows the collections that colour exists in.
- Usage & construction steps gate on the availability matrix; unavailable combos
  are shown but disabled with "Non disponible" (never hidden).
- Preview shows the matching product's image, falling back to a per-colour image
  in `public/assets/images/` when no product image is uploaded.
- Product pages deep-link `/configure?product=<slug>` to preload colour+collection.

## Key DB tables
`collections, colors, door_types (usages), construction_types, price_rules
(matrix), products (combinations + base_price), product_images, quote_requests,
quote_status_log, saved_configurations, price_calculations, media, settings,
admin_users` (+ auth: remember_tokens, rate_limits, activity_log).
Dormant/legacy (kept, not used by the live model): `materials, room_types,
optional_features, categories, product_materials, product_colors`.

## Security & conventions
- CSRF via `CsrfGuard`/`CsrfMiddleware` (header → `$_POST['_csrf']` → JSON body).
- Auth: `admin_users` bcrypt (cost 12), lockout, remember-me. Session `DS_ADMIN`.
- `SecurityHeaders`, `RateLimiter`, honeypot on the quote form, prepared statements.
- Code style: clean, no AI-narration comments, no decorative dividers/file headers.

## Migration history (database/, apply in order on a fresh DB)
auth, categories, products, pricing, quotes, configurator, colors_finishes, media,
room_types_migration, quote_workflow_migration, quote_hardening_migration,
contact_messages, then the PORTES phases:
- `phase1_schema.sql` / `phase1_seed.sql` — new model (construction_types,
  colours→collections, matrix, reference size).
- `phase2b_products.sql` — product width/height + construction.
- `phase6_real_catalog.sql` — purge demo, seed real catalogue.
- `phase7_color_model_fix.sql` — colours unique per collection; rename.
- `phase8_remove_finishes.sql` — drop the Finishes feature (tables/FKs/columns).
- `phase9_real_catalog_v2.sql` — final price reconciliation + Le Chêne.
- `phase10_products_as_combinations.sql` — products ARE combinations (57 rows).

## Notes / TODO
- Admin password is currently a test value — reset before production.
- Two products (Prestige · Porte d'Entrée · Tebelaire, Marron & Gris) are at
  price 0 pending the client's number — admin sets it in the matrix/products.
- `settings` table has no committed `CREATE TABLE` — verify it exists on fresh installs.

## Configurator UI — Luxury Redesign (current state, do not revert)

The configurator has been completely redesigned with a luxury light theme.
Files: `src/Views/configurator2.php`, `public/assets/css/configurator.css`,
`public/assets/js/configurator2.js`.

### Theme — CSS custom properties (scoped to `.cfg-body`)
```
--cfg-bg:      #f7f4ef   warm cream page background
--cfg-surface: #ffffff   card/panel surface
--cfg-text:    #1c1a17   primary text
--cfg-muted:   #7a7570   secondary/description text
--cfg-gold:    #c4a060   brand gold accent (borders, badges, highlights)
--cfg-gold-bg: #f5edd9   gold tint background
--cfg-border:  #e8e2d9   subtle dividers
--cfg-radius:  12px      default card border-radius
--cfg-hdr-h:   72px      header height (used for sticky offset)
```
Serif class variable `--serif` used for collection names and step titles.
Do NOT add dark-mode styles or override these on `.cfg-body`.

### Layout — Steps 1–7 (two-column)
`.cfg-page` is a CSS grid: `65fr 35fr` on ≥960px.
- Left (`.cfg-content`): current step content.
- Right (`aside.cfg-sidebar#cfgSummary`): sticky panel with door preview
  (`#cfgRender`) and configuration summary DL + price + action buttons.
- Mobile (<960px): single column; sidebar becomes a fixed bottom-sheet
  drawer (`.is-open` toggled by FAB button `#cfgSummaryBtn`).

### Layout — Step 0 (Collection): full-width immersive
When `showStep(0)` is called, JS adds class `is-collection-step` to `.cfg-page`.
CSS rules for that class:
- Override grid to `1fr` (no sidebar column), max-width 1340px centered.
- Hide `.cfg-sidebar` and `.cfg-summary-fab` entirely.
- Center the step heading and lead paragraph.
- Force `#cfgCollections` to `repeat(3, 1fr)` on desktop.
- Expand `.cfg-col-visual` to 360px tall (vs 300px base).
When user advances to Step 1+, `showStep(n)` removes the class and restores the
65/35 grid with sidebar automatically.

### Collection cards (built by JS `buildCollections()`)
Each `<button class="cfg-opt">` contains:
1. `.cfg-col-visual.cfg-col-visual--{slug}` — tall gradient area. If `col.img`
   exists in API data it becomes `background-image`; otherwise the gradient
   shows. Contains:
   - `.cfg-col-art-svg` — architectural SVG door illustration (defined in
     `COLL_ART` object in JS: panelled arch for Heritage, minimal for Moderne,
     double arch for Prestige). Scales up on hover.
   - `.cfg-col-overlay` — bottom-weighted gradient overlay.
   - `.cfg-col-num` — collection index "01/02/03" top-left (serif, faded).
   - `.cfg-col-tag` — "Collection" category label top-right (hidden behind badge
     when selected).
2. `.cfg-col-footer` — cream background below the visual:
   - `.cfg-col-sep` — 1px separator line; turns gold when selected.
   - `.cfg-col-name` — large (1.8rem) serif uppercase name; turns gold when
     selected.
   - `.cfg-col-desc` — short description in muted text.
3. `.cfg-col-bar` — absolute gold top bar that slides in (scaleX) on selection.
4. `.cfg-col-check` — gold circular checkmark badge, springs in on selection.

Selection state class: `is-active` added by `selectCollection()`.
Gradient backgrounds per slug:
- `heritage` → amber/oak tones (`#a07828 → #1e1508`)
- `moderne`  → charcoal (`#585858 → #0e0e0e`)
- `prestige` → deep gold (`#c49640 → #221608`)

### Progress bar
8 internal steps (S_COLLECTION=0 … S_DETAILS=7). Step 8 (quote form) is hidden
from the visible progress bar via CSS:
`#cfgProgress > *:nth-child(14), #cfgProgress > *:nth-child(15) { display:none }`
So users see 7 steps.

### Key JS objects and functions
- `COLL_DESCS` / `COLL_SLUGS` / `COLL_ART` — lookup objects for per-collection
  copy, CSS slug and SVG art.
- `showStep(n)` — single source of truth for step navigation; also toggles
  `is-collection-step` on `.cfg-page`.
- `setSummary()` — updates sidebar summary panel (collection, colour, usage,
  construction, dimensions, price).
- `paintPrice(label)` — updates all price display elements.
- `buildCollections()` — renders collection cards into `#cfgCollections`.
- `buildColors()` / `buildProducts()` — similar builders for other steps.
- All DOM lookups via `id(x)` helper (`getElementById` wrapper).

### Do NOT modify without care
- The 62 JS-critical element IDs in `configurator2.php` — all must stay in DOM.
- API response shape from `ConfiguratorController` (especially `total_price_fmt`,
  `total_price`, `currency` consumed by `paintPrice`).
- DB schema, business logic, pricing engine, admin panel.
- `CsrfGuard`, `SecurityHeaders`, rate limiting.
