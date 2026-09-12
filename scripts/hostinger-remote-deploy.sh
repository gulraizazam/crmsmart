#!/bin/bash
# Run on aestheticlinics Hostinger after pushing branch aestheticlinics.
set -euo pipefail
APP="${HOSTINGER_APP:-/home/u744025943/crmsmart}"
WEB="${HOSTINGER_WEB:-/home/u744025943/domains/aestheticlinics.net/public_html/crm}"
PHP="php -d disable_functions="
COMPOSER=/usr/local/bin/composer

cd "$APP"
git pull origin aestheticlinics || git pull github aestheticlinics || git pull

$PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs storage/app/public
chmod -R ug+rwX bootstrap/cache storage

cp -a public/. "$WEB/"
cp "$APP/scripts/hostinger-web-index.php" "$WEB/index.php" 2>/dev/null || true

$PHP artisan package:discover --ansi || true
$PHP artisan config:clear
$PHP artisan view:clear

echo "Deployed $(git rev-parse --short HEAD) to $WEB"
