#!/usr/bin/env bash
# =============================================================================
# Casa — One-time server setup for a fresh Ubuntu 24.04 LTS DigitalOcean droplet
# Run via: ./deploy.sh --setup
# =============================================================================
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-/var/www/casa}"
SHARED_PATH="${DEPLOY_PATH}/shared"
PHP_VERSION="8.4"
MYSQL_ROOT_PASS=$(openssl rand -hex 16)
DB_NAME="casa"
DB_USER="casa"
DB_PASS=$(openssl rand -hex 16)

echo ""
echo "═══════════════════════════════════════════"
echo "  Casa Server Setup"
echo "═══════════════════════════════════════════"
echo ""

# ── System packages ───────────────────────────────────────────────────────────
echo "▶  Updating system packages…"
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq \
  curl wget unzip git software-properties-common \
  nginx certbot python3-certbot-nginx \
  supervisor ufw

# ── PHP 8.4 ───────────────────────────────────────────────────────────────────
echo "▶  Installing PHP ${PHP_VERSION}…"
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
  php${PHP_VERSION}-fpm \
  php${PHP_VERSION}-cli \
  php${PHP_VERSION}-mysql \
  php${PHP_VERSION}-mbstring \
  php${PHP_VERSION}-xml \
  php${PHP_VERSION}-curl \
  php${PHP_VERSION}-zip \
  php${PHP_VERSION}-gd \
  php${PHP_VERSION}-intl \
  php${PHP_VERSION}-bcmath \
  php${PHP_VERSION}-tokenizer \
  php${PHP_VERSION}-fileinfo

# PHP-FPM settings
sed -i 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 25M/' /etc/php/${PHP_VERSION}/fpm/php.ini
sed -i 's/^post_max_size = .*/post_max_size = 26M/' /etc/php/${PHP_VERSION}/fpm/php.ini
systemctl enable php${PHP_VERSION}-fpm
systemctl start php${PHP_VERSION}-fpm
echo "   ✓ PHP ${PHP_VERSION} ready"

# ── Composer ──────────────────────────────────────────────────────────────────
echo "▶  Installing Composer…"
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  chmod +x /usr/local/bin/composer
fi
echo "   ✓ Composer $(composer --version --no-ansi | cut -d' ' -f3)"

# ── MySQL ─────────────────────────────────────────────────────────────────────
echo "▶  Installing MySQL 8…"
apt-get install -y -qq mysql-server

# Secure MySQL and create app database
mysql -u root <<MYSQL
  ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASS}';
  CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
  FLUSH PRIVILEGES;
MYSQL

systemctl enable mysql
echo "   ✓ MySQL ready"
echo "   ✓ Database: ${DB_NAME} | User: ${DB_USER} | Pass: ${DB_PASS}"

# ── Directory structure ───────────────────────────────────────────────────────
echo "▶  Creating deploy directory structure…"
mkdir -p "${DEPLOY_PATH}/releases"
mkdir -p "${SHARED_PATH}/storage/app/public"
mkdir -p "${SHARED_PATH}/storage/framework/cache/data"
mkdir -p "${SHARED_PATH}/storage/framework/sessions"
mkdir -p "${SHARED_PATH}/storage/framework/views"
mkdir -p "${SHARED_PATH}/storage/logs"
touch "${SHARED_PATH}/storage/logs/laravel.log"

chown -R www-data:www-data "${DEPLOY_PATH}"
chmod -R 775 "${SHARED_PATH}/storage"
echo "   ✓ Deploy dirs ready at ${DEPLOY_PATH}"

# ── Nginx ─────────────────────────────────────────────────────────────────────
echo "▶  Configuring Nginx…"
cp /dev/stdin /etc/nginx/sites-available/casa <<'NGINX'
server {
    listen 80;
    server_name _;

    root /var/www/casa/current/public;
    index index.php;

    client_max_body_size 26M;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    access_log /var/log/nginx/casa-access.log;
    error_log  /var/log/nginx/casa-error.log;
}
NGINX

ln -sf /etc/nginx/sites-available/casa /etc/nginx/sites-enabled/casa
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx
systemctl reload nginx
echo "   ✓ Nginx configured"

# ── Firewall ──────────────────────────────────────────────────────────────────
echo "▶  Configuring firewall…"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable
echo "   ✓ Firewall: SSH + HTTP/HTTPS allowed"

# ── .env reminder ────────────────────────────────────────────────────────────
echo ""
echo "═══════════════════════════════════════════"
echo "  ✅  Server setup complete!"
echo "═══════════════════════════════════════════"
echo ""
echo "  Next: create the .env file on the server:"
echo ""
echo "    scp .env.production root@YOUR_IP:${SHARED_PATH}/.env"
echo ""
echo "  Use these credentials in your .env:"
echo ""
echo "    DB_HOST=127.0.0.1"
echo "    DB_DATABASE=${DB_NAME}"
echo "    DB_USERNAME=${DB_USER}"
echo "    DB_PASSWORD=${DB_PASS}"
echo ""
echo "  MySQL root password (save this somewhere safe):"
echo "    ${MYSQL_ROOT_PASS}"
echo ""
echo "  Then run ./deploy.sh to push the app."
echo ""
