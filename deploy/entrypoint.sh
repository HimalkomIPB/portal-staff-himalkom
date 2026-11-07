#!/bin/sh
# Does NOT run migrations (run `php artisan migrate --force` manually, once, after DB review).
set -e
cd /var/www/html
: "${APP_KEY:?APP_KEY must be set}"
su -s /bin/sh www-data -c 'php artisan storage:link --force >/dev/null && php artisan optimize && (php artisan filament:optimize || true)'
php-fpm -D
exec nginx -g 'daemon off;'
