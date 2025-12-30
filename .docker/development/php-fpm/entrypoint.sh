#!/bin/sh
set -e

php artisan key:generate --force || true
php artisan migrate --force || true
php artisan optimize || true

exec "$@"
