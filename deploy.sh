#!/usr/bin/env bash
# Run on the server (Cloudways SSH) from the application folder after pulling new code:
#   bash deploy.sh
# First install only: create .env from .env.example, then run `php artisan key:generate`
# and `php artisan migrate --force --seed` once before using this script.
set -e

php artisan down --retry=30 || true

composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan up
echo "Deployed."
