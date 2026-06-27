# Deploying PORTES to InfinityFree

InfinityFree runs PHP 8 + MySQL together (classic shared hosting). The app is
served from a `/door-showroom/` subfolder so its hardcoded paths keep working.

## 1. Create the hosting account + database
1. Sign up at infinityfree.com and create a hosting account for your domain
   (e.g. `yoursite.infinityfreeapp.com`).
2. In the client area → **MySQL Databases** → create a database. Note the
   four values it shows:
   - **MySQL host** (e.g. `sql123.infinityfree.com`)
   - **Database name** (e.g. `epiz_12345678_doors`)
   - **Username** (e.g. `epiz_12345678`)
   - **Password** (the one you set / it shows)

## 2. Set the DB credentials
1. Copy `config/database.local.example.php` → `config/database.local.php`.
2. Fill in the four values from step 1.
   (This file is gitignored and overrides everything else. InfinityFree has no
   environment variables, so this file is how the app gets its credentials.)

## 3. Upload the files (FTP or the online File Manager)
Upload so the structure under the web root (`htdocs/`) is:

```
htdocs/
├── .htaccess                 ← copy of deploy/htdocs-root.htaccess
└── door-showroom/            ← the WHOLE project folder
    ├── .htaccess             ← the project-root .htaccess (already in repo)
    ├── public/  src/  config/  database/  storage/  ...
    └── config/database.local.php   ← your filled-in credentials
```

- Put the project (everything in this repo) inside `htdocs/door-showroom/`.
- Put `deploy/htdocs-root.htaccess` at `htdocs/.htaccess` (rename it) so the
  bare domain redirects into the app.
- You can skip uploading: `.git/`, `deploy/`, `*.md`, `memory/`, `.claude/`.

## 4. Import the database
1. Client area → **phpMyAdmin** for your database.
2. Import the SQL files **in this order** (Import tab, one file at a time):

   ```
   auth.sql
   categories.sql
   products.sql
   pricing.sql
   pricing_seed.sql
   quotes.sql
   configurator.sql
   colors_finishes.sql
   media.sql
   room_types_migration.sql
   quote_workflow_migration.sql
   quote_hardening_migration.sql
   contact_messages.sql
   phase1_schema.sql
   phase1_seed.sql
   phase2b_products.sql
   phase6_real_catalog.sql
   phase7_color_model_fix.sql
   phase8_remove_finishes.sql
   phase9_real_catalog_v2.sql
   phase10_products_as_combinations.sql
   ```
   (This is the full list in `database/`, in dependency order matching the
   migration history in CLAUDE.md.)

## 5. Visit the site
- `https://yoursite.infinityfreeapp.com/`  → redirects to `/door-showroom/`
- Admin: `https://yoursite.infinityfreeapp.com/door-showroom/admin`

## Notes / limits
- Reset the admin password before sharing the URL (it is a test value).
- Make sure `public/uploads/` and `storage/` are writable (the File Manager →
  permissions → 0755 or 0777 on those folders) so quotes/uploads work.
- InfinityFree free tier has daily hit limits and no SSH; fine for a showcase.
- The Vercel files (`vercel.json`, `api/`) are unused on InfinityFree; harmless.
