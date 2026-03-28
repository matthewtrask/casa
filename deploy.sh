#!/usr/bin/env bash
# =============================================================================
# Casa — Deploy script
# Usage:
#   ./deploy.sh                   # deploy to server defined in deploy.config
#   ./deploy.sh --setup           # first-time server setup, then deploy
#   ./deploy.sh --rollback        # revert to the previous release
# =============================================================================
set -euo pipefail

# ── Config ───────────────────────────────────────────────────────────────────
CONFIG_FILE="$(dirname "$0")/deploy.config"
if [[ ! -f "$CONFIG_FILE" ]]; then
  echo "❌  deploy.config not found. Copy deploy.config.example and fill it in."
  exit 1
fi
source "$CONFIG_FILE"

# Required vars (checked after sourcing config)
: "${SERVER_HOST:?deploy.config must define SERVER_HOST}"
: "${SERVER_USER:?deploy.config must define SERVER_USER}"
: "${DEPLOY_PATH:?deploy.config must define DEPLOY_PATH}"
: "${SSH_KEY:?deploy.config must define SSH_KEY}"

SSH="ssh -i $SSH_KEY -o StrictHostKeyChecking=accept-new ${SERVER_USER}@${SERVER_HOST}"
RELEASES_PATH="${DEPLOY_PATH}/releases"
SHARED_PATH="${DEPLOY_PATH}/shared"
CURRENT_LINK="${DEPLOY_PATH}/current"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
RELEASE_PATH="${RELEASES_PATH}/${TIMESTAMP}"

# ── Helpers ──────────────────────────────────────────────────────────────────
step()  { echo ""; echo "▶  $1"; }
ok()    { echo "   ✓ $1"; }
warn()  { echo "   ⚠  $1"; }

# ── Flags ────────────────────────────────────────────────────────────────────
DO_SETUP=false
DO_ROLLBACK=false
for arg in "$@"; do
  case $arg in
    --setup)    DO_SETUP=true ;;
    --rollback) DO_ROLLBACK=true ;;
  esac
done

# ── Rollback ─────────────────────────────────────────────────────────────────
if $DO_ROLLBACK; then
  step "Rolling back to previous release…"
  $SSH bash <<'ENDSSH'
    RELEASES=$(ls -1t $DEPLOY_PATH/releases 2>/dev/null)
    COUNT=$(echo "$RELEASES" | wc -l)
    if [[ $COUNT -lt 2 ]]; then
      echo "No previous release to roll back to."
      exit 1
    fi
    PREV=$(echo "$RELEASES" | sed -n '2p')
    ln -sfn "$DEPLOY_PATH/releases/$PREV" "$DEPLOY_PATH/current"
    cd "$DEPLOY_PATH/current"
    php artisan config:clear
    php artisan view:clear
    sudo systemctl reload php8.4-fpm
    echo "Rolled back to $PREV"
ENDSSH
  ok "Rollback complete."
  exit 0
fi

# ── One-time server setup ─────────────────────────────────────────────────────
if $DO_SETUP; then
  step "Running one-time server setup…"
  $SSH "bash -s" < "$(dirname "$0")/deploy/server-setup.sh"
  ok "Server setup complete. Now running initial deploy…"
fi

# ── Pre-flight checks ─────────────────────────────────────────────────────────
step "Pre-flight checks"

# Make sure .env exists on the server in shared/
ENV_CHECK=$($SSH "test -f ${SHARED_PATH}/.env && echo yes || echo no")
if [[ "$ENV_CHECK" == "no" ]]; then
  warn "No .env found at ${SHARED_PATH}/.env on the server."
  warn "Create it before continuing (see deploy/env.example)."
  warn "Run: scp -i $SSH_KEY .env.production ${SERVER_USER}@${SERVER_HOST}:${SHARED_PATH}/.env"
  echo ""
  read -rp "   .env is not set up. Continue anyway? (y/N): " confirm
  [[ "$confirm" =~ ^[Yy]$ ]] || exit 1
fi
ok "Server reachable, shared .env present"

# ── Build: package local assets ───────────────────────────────────────────────
step "Installing Composer dependencies (no dev)"
composer install --no-dev --optimize-autoloader --quiet
ok "Dependencies ready"

# ── Sync to new release directory ─────────────────────────────────────────────
step "Uploading release ${TIMESTAMP}…"
$SSH "mkdir -p ${RELEASE_PATH}"

rsync -az --delete \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='node_modules' \
  --exclude='storage/logs' \
  --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' \
  --exclude='storage/framework/views' \
  --exclude='tests' \
  --exclude='deploy' \
  --exclude='docker-compose.yml' \
  --exclude='*.md' \
  -e "ssh -i ${SSH_KEY} -o StrictHostKeyChecking=accept-new" \
  ./ "${SERVER_USER}@${SERVER_HOST}:${RELEASE_PATH}/"

ok "Files synced"

# ── Wire up shared files & dirs ───────────────────────────────────────────────
step "Linking shared .env and storage…"
$SSH bash << ENDSSH
  set -e
  # .env
  ln -sf ${SHARED_PATH}/.env ${RELEASE_PATH}/.env

  # storage (logs, sessions, caches, uploads)
  rm -rf ${RELEASE_PATH}/storage
  ln -sf ${SHARED_PATH}/storage ${RELEASE_PATH}/storage

  # bootstrap/cache must be writable
  mkdir -p ${RELEASE_PATH}/bootstrap/cache
  chmod -R 775 ${RELEASE_PATH}/bootstrap/cache
ENDSSH
ok "Shared links in place"

# ── Run Laravel post-deploy commands ─────────────────────────────────────────
step "Running artisan commands…"
$SSH bash << ENDSSH
  set -e
  cd ${RELEASE_PATH}

  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan storage:link --force

  # Set permissions
  chown -R www-data:www-data ${RELEASE_PATH}
  find ${RELEASE_PATH} -type f -exec chmod 644 {} \;
  find ${RELEASE_PATH} -type d -exec chmod 755 {} \;
  chmod 755 ${RELEASE_PATH}/artisan
ENDSSH
ok "Artisan commands complete"

# ── Swap the current symlink (atomic cutover) ─────────────────────────────────
step "Activating release…"
$SSH "ln -sfn ${RELEASE_PATH} ${CURRENT_LINK}"
ok "Release ${TIMESTAMP} is now live"

# ── Reload PHP-FPM & Nginx ────────────────────────────────────────────────────
step "Reloading services…"
$SSH "sudo systemctl reload php8.4-fpm && sudo systemctl reload nginx"
ok "Services reloaded"

# ── Schedule: make sure cron is wired up ─────────────────────────────────────
step "Checking scheduler cron…"
CRON_LINE="* * * * * ${SERVER_USER} php ${CURRENT_LINK}/artisan schedule:run >> /dev/null 2>&1"
$SSH bash << ENDSSH
  CRON_FILE="/etc/cron.d/casa"
  if [[ ! -f "\$CRON_FILE" ]]; then
    echo "${CRON_LINE}" | sudo tee "\$CRON_FILE" > /dev/null
    sudo chmod 644 "\$CRON_FILE"
    echo "   ✓ Cron job installed"
  else
    echo "   ✓ Cron job already in place"
  fi
ENDSSH

# ── Clean up old releases (keep last 5) ───────────────────────────────────────
step "Pruning old releases (keeping 5)…"
$SSH bash << 'ENDSSH'
  cd "${RELEASES_PATH}" 2>/dev/null || exit 0
  ls -1t | tail -n +6 | xargs -r rm -rf
  echo "   ✓ Old releases cleaned"
ENDSSH

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo "✅  Deploy complete!"
echo "    Release: ${TIMESTAMP}"
echo "    URL:     http://${SERVER_HOST}"
echo ""
echo "   Tip: if something looks wrong, rollback with: ./deploy.sh --rollback"
echo ""
