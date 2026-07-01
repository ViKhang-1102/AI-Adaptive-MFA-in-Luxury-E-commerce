# syntax=docker/dockerfile:1

FROM composer:2.8 AS build
WORKDIR /var/www/html

COPY composer.json composer.lock* ./
COPY . ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader


FROM php:8.2-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APP_ENV=production \
    APP_DEBUG=false \
    PORT=10000

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl libpq-dev libzip-dev libicu-dev libonig-dev libxml2-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring bcmath intl zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && a2enmod rewrite headers expires \
    && printf 'opcache.enable=1\nopcache.enable_cli=0\nopcache.memory_consumption=128\nopcache.max_accelerated_files=10000\nopcache.validate_timestamps=0\nopcache.fast_shutdown=1\n' > /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
