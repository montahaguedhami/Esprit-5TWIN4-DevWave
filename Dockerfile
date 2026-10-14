# ─── Stage 1 : build des assets front-end ─────────────────────────────────────
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json ./
RUN npm install

COPY . .
RUN npm run build

# ─── Stage 2 : image PHP de production ────────────────────────────────────────
FROM php:8.2-fpm-alpine

# Dépendances système
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    openssl

# Extensions PHP nécessaires
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        intl \
        opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP (sans les dev, sans scripts)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Code source complet
COPY . .

# Assets compilés depuis le stage node
COPY --from=node-builder /app/public/build ./public/build

# Finalise l'autoloader et exécute les scripts post-install
RUN composer dump-autoload --optimize \
 && php artisan package:discover --ansi

# Permissions storage / cache
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Certificat CA Aiven MySQL
COPY docker/mysql/ca.pem /etc/ssl/certs/aiven-mysql-ca.pem

# Configuration nginx minimale pour Render (écoute sur $PORT)
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
