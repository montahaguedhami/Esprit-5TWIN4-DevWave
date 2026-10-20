# AquaSecure — image de l'application (PHP-FPM + Nginx + worker de file + planificateur)
#
#   docker build -t aquasecure-app .
#   docker build --build-arg WITH_DEMO_DATA=false -t aquasecure-app .   (sans dépendances de dev)
#
# Étapes : base (PHP + extensions) → vendor (Composer) → assets (Vite) → image finale

# ---------------------------------------------------------------------------
# 1. Base : PHP 8.3-FPM avec les extensions utilisées par l'application
# ---------------------------------------------------------------------------
FROM php:8.3.35-fpm-alpine AS base

RUN apk add --no-cache icu-libs libpng libjpeg-turbo libwebp freetype libzip oniguruma \
 && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libpng-dev libjpeg-turbo-dev \
        libwebp-dev freetype-dev libzip-dev oniguruma-dev linux-headers \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring intl gd zip bcmath exif pcntl opcache \
 && apk del .build-deps

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# 2. Dépendances PHP (Composer)
#    WITH_DEMO_DATA=true garde fakerphp/faker (dev), nécessaire aux seeders de démonstration
# ---------------------------------------------------------------------------
FROM base AS vendor

ARG WITH_DEMO_DATA=true
COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN if [ "$WITH_DEMO_DATA" = "true" ]; then FLAGS=""; else FLAGS="--no-dev"; fi \
 && composer install $FLAGS --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN if [ "$WITH_DEMO_DATA" = "true" ]; then FLAGS=""; else FLAGS="--no-dev"; fi \
 && composer dump-autoload $FLAGS --optimize --classmap-authoritative --no-interaction

# ---------------------------------------------------------------------------
# 3. Assets front (Vite + Tailwind) — Tailwind lit aussi les vues de pagination dans vendor/
# ---------------------------------------------------------------------------
FROM node:22.23.3-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# ---------------------------------------------------------------------------
# 4. Image finale
# ---------------------------------------------------------------------------
FROM base AS app

RUN apk add --no-cache nginx supervisor curl \
 && mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/nginx \
 && chown -R www-data:www-data /run/nginx /var/lib/nginx /var/log/nginx

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-aquasecure.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-aquasecure.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

COPY --from=vendor /var/www/html /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chmod +x /usr/local/bin/entrypoint.sh \
 && rm -f public/hot bootstrap/cache/*.php \
 && php artisan package:discover --ansi \
 && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs \
 && ln -s ../storage/app/public public/storage \
 && chown -R www-data:www-data storage bootstrap/cache

# Aucun processus ne tourne en root (Nginx écoute sur un port non privilégié)
USER www-data

EXPOSE 8080

HEALTHCHECK --interval=15s --timeout=5s --start-period=60s --retries=5 \
    CMD curl -fs http://localhost:8080/up > /dev/null || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
