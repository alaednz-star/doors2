#!/usr/bin/env bash
# Production-safe storage setup for PORTES.
# Run once after deployment (and after pulling changes that add storage dirs).
#
#   sudo bash bin/setup-storage.sh [web_user]
#
# Creates the writable storage directories the app needs and assigns them to the
# web server user so logging, mail spooling and uploads never fail at runtime.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WEB_USER="${1:-www-data}"

DIRS=(
  "storage/logs"      # application + failure logs (Logger)
  "storage/mail"      # spooled email when SMTP is off (Mailer)
  "storage/products"  # uploaded product imagery
  "public/uploads"    # public upload target (if used)
)

echo "PORTES storage setup → root: $ROOT  web user: $WEB_USER"

for d in "${DIRS[@]}"; do
  path="$ROOT/$d"
  mkdir -p "$path"
  # Owner = web user so the app can write; group-writable; not world-writable.
  if id "$WEB_USER" >/dev/null 2>&1; then
    chown -R "$WEB_USER":"$WEB_USER" "$path" 2>/dev/null || true
  fi
  chmod -R 2775 "$path"
  echo "  ✓ $d"
done

# Protect log/mail spool from direct web access.
for d in storage/logs storage/mail; do
  cat > "$ROOT/$d/.htaccess" <<'HT'
Require all denied
HT
done

echo "Done. Storage is production-ready."
