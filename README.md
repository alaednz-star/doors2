# PORTES — Architectural Door Showroom & Configurator

A luxury made-to-measure door e-commerce / configurator for an Algerian client
(brand **PORTES**). Customers browse collections, configure a door through a
guided flow, and request a personal quote. Admins manage the full catalogue,
pricing matrix, products and quotes through a custom admin console. Prices in
**DZD**.

> **AI assistants:** read [`CLAUDE.md`](CLAUDE.md) first — it is the authoritative
> guide to the business model, architecture and conventions.

---

## Tech stack

| Layer | Choice |
|---|---|
| Language | **PHP 8.3**, no framework |
| Routing | Custom front controllers + a PSR-4-style autoloader (`App\` → `src/`) |
| Database | **MySQL 8 / MariaDB** (DB name `door_showroom`) |
| Frontend | Server-rendered PHP views + **vanilla JS** (no build step) |
| Styling | Per-page CSS in `public/assets/css/` |
| Server | Apache, served at the path prefix `/door-showroom` |

No Composer, no npm, no build pipeline — clone, create the DB, point a webroot at
`public/`, and it runs.

---

## Quick start (local)

This was developed on **XAMPP/LAMPP** but any PHP 8.3 + MySQL works.

```bash
# 1. Clone
git clone git@github.com:alaednz-star/doors.git
cd doors

# 2. Create the database and load schema + real catalogue (in order).
mysql -u root -e "CREATE DATABASE door_showroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root door_showroom < database/auth.sql
mysql -u root door_showroom < database/categories.sql
mysql -u root door_showroom < database/products.sql
mysql -u root door_showroom < database/pricing.sql
mysql -u root door_showroom < database/quotes.sql
mysql -u root door_showroom < database/configurator.sql
mysql -u root door_showroom < database/colors_finishes.sql
mysql -u root door_showroom < database/media.sql
mysql -u root door_showroom < database/room_types_migration.sql
mysql -u root door_showroom < database/quote_workflow_migration.sql
mysql -u root door_showroom < database/quote_hardening_migration.sql
mysql -u root door_showroom < database/contact_messages.sql
# PORTES real-model phases (run in this order):
mysql -u root door_showroom < database/phase1_schema.sql
mysql -u root door_showroom < database/phase1_seed.sql
mysql -u root door_showroom < database/phase2b_products.sql
mysql -u root door_showroom < database/phase6_real_catalog.sql
mysql -u root door_showroom < database/phase7_color_model_fix.sql
mysql -u root door_showroom < database/phase8_remove_finishes.sql
mysql -u root door_showroom < database/phase9_real_catalog_v2.sql
mysql -u root door_showroom < database/phase10_products_as_combinations.sql

# 3. Configure DB credentials
#    edit config/database.php (host/port/name/user/pass)

# 4. Make uploads writable by the web server user (e.g. www-data)
chmod -R 0777 public/uploads

# 5. Serve. With XAMPP/LAMPP place the project at htdocs/door-showroom,
#    or run PHP's built-in server from the project root:
php -S localhost:8000 -t public
```

On XAMPP/LAMPP the migration files are applied with the bundled client, e.g.
`/opt/lampp/bin/mysql -u root door_showroom < database/<file>.sql`.

### URLs
- Public site: `http://localhost/door-showroom`
- Configurator: `http://localhost/door-showroom/configure`
- Admin: `http://localhost/door-showroom/admin`

Seed admin login: **`admin@showroom.dz`** (the default password hash ships in
`database/auth.sql` — **reset it before any production use**).

---

## The business model (important)

The catalogue is built from these entities **only**:

**Collections · Colours · Products · Door Usages · Construction Types ·
Pricing Matrix · Quotes · Settings**

There is **no "Finish" and no "Material" concept** — those were demo/template
features and have been removed from the database and the UI.

- **Collections** — Heritage, Moderne, Prestige (real catalogue lines).
- **Colours** belong to exactly ONE collection (`colors.collection_id`). A colour
  name is unique *within* a collection, not globally — e.g. "Gris" exists in both
  Heritage and Prestige (`uk_colors_collection_name`).
  - Prestige → Marron, Gris · Moderne → Scuro, Simza, Madera, Wengue, Serya ·
    Heritage → Chêne, Gris
- **Door Usages** (table `door_types`) — Chambre, Sanitaire, Salon, Porte d'Entrée.
- **Construction Types** — Nédabaile, Tebelaire, PVC.
- **Pricing Matrix** (`price_rules`) — one row per
  `Collection × Usage × Construction` → `base_price` + `is_available`. The source
  of truth for price and availability. Fully admin-editable, nothing hardcoded.
- **Products = sellable combinations.** A product IS one available
  `Collection × Colour × Usage × Construction` + price. They are generated from
  the *available* matrix cells × each collection's colours (57 products).
  Unavailable combinations are not products.

### Pricing formula
```
cell = price_rules row for (collection, usage, construction), is_active = 1
if !cell or is_available = 0  →  "Non disponible"
referenceArea = pricing_ref_width_mm × pricing_ref_height_mm   (settings; default 900×2100)
finalPrice    = round(cell.base_price × (width × height) / referenceArea, 2)
```
Implemented in `src/Services/PricingCalculator.php` —
`calculate(array $input): array` (keep this signature stable).

---

## Configurator flow

`/door-showroom/configure` — colour/availability-aware, 7 steps:

**Collection → Color → Usage → Construction → Door Design → Dimensions → Review & Quote**

1. Choose a collection (DB).
2. Choose a colour — only colours of that collection are shown.
3. Choose usage — unavailable combos are **disabled, not hidden**, labelled
   **"Non disponible"**.
4. Choose construction — same availability gating.
5. **Door Design** — products matching the full
   `Collection + Colour + Usage + Construction` are shown with their real images;
   auto-preselected when only one matches.
6. Dimensions — width/height with live price.
7. Review & Quote — full summary, final price, quote form.

Product pages deep-link `/door-showroom/configure?product=<slug>` to preload the
collection + colour.

Files: view `src/Views/configurator2.php`, JS
`public/assets/js/configurator2.js`, backend
`src/Controllers/ConfiguratorController.php`.

---

## Project structure

```
config/                 app.php, database.php, security.php
database/               raw SQL schema + idempotent migration "phase" files
public/
  index.php             public front controller + routes
  admin.php             admin front controller + routes
  assets/{css,js,images}
  uploads/              user uploads (gitignored)
src/
  Controllers/          public page controllers
  Controllers/Admin/    admin CRUD controllers
  Core/                 Database (PDO), Config, Session, Logger
  Auth/                 Authenticator, CsrfGuard, RateLimiter
  Middleware/           AuthMiddleware, CsrfMiddleware, SecurityHeaders
  Services/             PricingCalculator, ImageUploader, Mailer, ConfigValidator
  Validators/           form validators
  Views/                page templates (admin/ + public)
CLAUDE.md               authoritative project guide for AI assistants
```

---

## Admin console

`/door-showroom/admin` — full CRUD for: Collections, Colours (grouped by
collection), Products (the 57 combinations), Door Usages, Construction Types, the
Pricing Matrix (price + availability toggles), Quotes (workflow + status log),
Media Library, and Settings (reference door size). Everything is DB-driven and
editable; new collections / colours / usages / constructions / products / prices
can all be added without code changes.

---

## Database migrations

Raw SQL, applied manually (no migration runner). Every PORTES `phaseN_*.sql` file
is **idempotent** (uses `information_schema` guards + `PREPARE`/`EXECUTE`), so
re-running is safe. Apply in the order listed in **Quick start** above. Highlights:

- `phase1_*` — new model (construction types, colours→collections, matrix, reference size)
- `phase2b_products.sql` — product width/height + construction
- `phase6_real_catalog.sql` — purge demo data, seed the real catalogue
- `phase7_color_model_fix.sql` — colours unique per collection + rename
- `phase8_remove_finishes.sql` — drop the Finishes feature entirely
- `phase9_real_catalog_v2.sql` — final price reconciliation
- `phase10_products_as_combinations.sql` — products ARE combinations (57 rows)

---

## Security

CSRF protection (header → `$_POST['_csrf']` → JSON body), bcrypt admin auth
(cost 12) with lockout + remember-me, `SecurityHeaders`, `RateLimiter`, a quote
honeypot, and prepared statements throughout.

---

## Known TODO / handover notes

- **Reset the admin password** before production (currently a dev/test value).
- Two products (Prestige · Porte d'Entrée · Tebelaire — Marron & Gris) are at
  price **0**, pending the client's number; set it in the admin pricing matrix.
- **PVC** construction has no pricing rows yet — admin can add them later.
- Colour/product images: real photos for the 57 products are uploaded per product
  via the admin; until then the configurator falls back to per-colour images in
  `public/assets/images/`.

---

## License

Proprietary — client project. Not for redistribution.
