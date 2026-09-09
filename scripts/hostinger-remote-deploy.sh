#!/bin/bash
# Run on Hostinger after pushing to GitHub: bash scripts/hostinger-remote-deploy.sh
set -euo pipefail
APP=/home/u744025943/crmsmart
WEB=/home/u744025943/domains/aestheticlinics.net/public_html/crm
PHP="php -d disable_functions="
COMPOSER=/usr/local/bin/composer

cd "$APP"
git pull origin main || git pull github main || git pull

$PHP $COMPOSER install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs storage/app/public
chmod -R ug+rwX bootstrap/cache storage

cp -a public/. "$WEB/"
cp "$APP/scripts/hostinger-web-index.php" "$WEB/index.php" 2>/dev/null || true

$PHP artisan package:discover --ansi || true
$PHP artisan config:clear
$PHP artisan view:clear

echo "Deployed $(git rev-parse --short HEAD) to $WEB"
