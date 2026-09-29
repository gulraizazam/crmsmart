#!/bin/bash
# crm.smartaesthetics.pk — run on that Hostinger box after pushing branch main.
set -euo pipefail
APP="${HOSTINGER_APP:-/home/u941750079/crmsmart}"
WEB="${HOSTINGER_WEB:-/home/u941750079/domains/smartaesthetics.pk/public_html/crm}"
PHP="php -d disable_functions="
COMPOSER=/usr/local/bin/composer

cd "$APP"
git pull origin main || git pull github main || git pull

$PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs storage/app/public
chmod -R ug+rwX bootstrap/cache storage

# CRM lives in public_html/crm. Do not copy into WordPress public_html.
cp -a public/. "$WEB/"
cp "$APP/scripts/hostinger-web-index.php" "$WEB/index.php" 2>/dev/null || true

$PHP artisan package:discover --ansi || true
$PHP artisan config:clear
$PHP artisan view:clear

echo "Deployed $(git rev-parse --short HEAD) to $WEB"
