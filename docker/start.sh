#!/bin/sh
set -e

# Cache config/routes/views pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrations (--force requis hors dev)
php artisan migrate --force

# Démarre php-fpm en arrière-plan
php-fpm -D

# Remplace $PORT dans nginx.conf et démarre nginx en avant-plan
PORT=${PORT:-8080}
sed -i "s/\${PORT:-8080}/$PORT/g" /etc/nginx/nginx.conf
exec nginx -g "daemon off;"
