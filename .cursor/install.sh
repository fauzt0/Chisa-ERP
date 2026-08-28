#!/usr/bin/env bash
#
# Cloud Agent install script for the Chisa ERP (CodeIgniter 3 / PHP 7.4 / MariaDB).
#
# Idempotent: safe to run repeatedly and against a cached/prebuilt snapshot.
# Heavy, network-dependent steps are guarded so a prebuilt environment skips them.
#
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_DIR"

DB_NAME="${CHISA_DB_NAME:-st32477_chisa}"
DB_USER="${CHISA_DB_USER:-chisa}"
DB_PASS="${CHISA_DB_PASS:-chisa}"
SEED_DUMP="doc/st32477_chisa_demo_proveedores.sql"

echo "==> [1/5] System packages (PHP 7.4, MariaDB, Composer)"
if ! command -v php7.4 >/dev/null 2>&1; then
  export DEBIAN_FRONTEND=noninteractive
  sudo apt-get update -y
  sudo apt-get install -y software-properties-common ca-certificates lsb-release
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -y
  sudo apt-get install -y \
    php7.4-cli php7.4-mysql php7.4-mbstring php7.4-xml php7.4-gd php7.4-zip \
    php7.4-curl php7.4-intl php7.4-bcmath php7.4-gmp \
    mariadb-server mariadb-client unzip curl
else
  echo "    PHP $(php7.4 -r 'echo PHP_VERSION;') already installed; skipping apt."
fi

if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  sudo php7.4 /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm -f /tmp/composer-setup.php
else
  echo "    Composer $(composer --version 2>/dev/null | awk '{print $3}') already installed; skipping."
fi

echo "==> [2/5] PHP dependencies (PhpSpreadsheet via Composer)"
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --no-progress
else
  echo "    vendor/ already present; skipping composer install."
fi

echo "==> [3/5] Local development config overrides"
mkdir -p application/config/development
cp -f .cursor/dev-config/database.php application/config/development/database.php
cp -f .cursor/dev-config/config.php   application/config/development/config.php

echo "==> [4/5] Start MariaDB and ensure database + user exist"
sudo service mariadb start
for _ in $(seq 1 30); do
  if sudo mysqladmin ping >/dev/null 2>&1; then break; fi
  sleep 1
done

sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

echo "==> [5/5] Import seed database (only if empty)"
TABLE_COUNT="$(sudo mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")"
if [ "${TABLE_COUNT}" -eq 0 ]; then
  echo "    Importing ${SEED_DUMP} into ${DB_NAME} ..."
  sudo mysql "${DB_NAME}" < "${SEED_DUMP}"
  echo "    Imported. Tables now: $(sudo mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")"
else
  echo "    ${DB_NAME} already has ${TABLE_COUNT} tables; skipping import."
fi

echo "install.sh complete. Demo login: presentacion@chisa.mx / Demo2026!"
