# Production image (nginx + PHP-FPM 8.3 in one container), built by Coolify from this repo (build context = repo root).
# The container does NOT run migrations. Runtime config (.env values, APP_KEY, DB) comes from Coolify env vars only.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --no-progress --prefer-dist \
        --ignore-platform-req=ext-intl --ignore-platform-req=ext-gd --ignore-platform-req=ext-zip --ignore-platform-req=ext-bcmath \
 && composer audit --locked --no-dev

# Vite assets (public/build is not in git). Tailwind scans vendor/ views, so vendor is copied in first.
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts --no-audit --no-fund
COPY . ./
COPY --from=vendor /app/vendor ./vendor
RUN npm run build && test -f public/build/manifest.json

FROM vendor AS app
COPY . ./
COPY --from=assets /app/public/build ./public/build
RUN rm -rf vendor/bin/.phpunit* node_modules .env bootstrap/cache/*.php \
 && composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts \
 && test -f public/build/manifest.json

FROM php:8.3-fpm-alpine
RUN apk add --no-cache nginx icu-libs libzip libpng freetype libjpeg-turbo \
 && apk add --no-cache --virtual .build icu-dev libzip-dev libpng-dev freetype-dev libjpeg-turbo-dev $PHPIZE_DEPS \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j1 intl zip gd bcmath pdo_mysql opcache \
 && apk del .build && rm -rf /tmp/* /usr/src/*
COPY deploy/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY deploy/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY deploy/nginx.conf /etc/nginx/nginx.conf
COPY deploy/entrypoint.sh /entrypoint.sh
COPY --from=app --chown=www-data:www-data /app /var/www/html
WORKDIR /var/www/html
RUN rm -f public/storage && chmod 0555 /entrypoint.sh \
 && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s CMD wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1
ENTRYPOINT ["/entrypoint.sh"]
