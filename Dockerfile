# syntax=docker/dockerfile:1
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions required by Laravel
RUN apk add --no-cache \
        bash \
        git \
        curl \
        libpng libpng-dev \
        libjpeg-turbo libjpeg-turbo-dev \
        freetype freetype-dev \
        libzip libzip-dev \
        oniguruma oniguruma-dev \
        icu icu-dev \
        zlib zlib-dev \
        shadow \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo pdo_mysql zip gd intl mbstring opcache \
    && apk del --no-network freetype-dev libpng-dev libjpeg-turbo-dev icu-dev zlib-dev oniguruma-dev

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy only composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-interaction --no-ansi --no-progress --prefer-dist --no-scripts \
    || composer install --no-interaction --no-ansi --no-progress --prefer-dist --no-scripts

# Copy application source
COPY . .

# Ensure storage and bootstrap/cache are writable
RUN addgroup -S laravel && adduser -S -G laravel laravel \
    && chown -R laravel:laravel storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

RUN chmod -R 755 storage/logs

USER laravel

EXPOSE 9000

# Default command kept minimal; php-fpm will be launched by service
CMD ["php-fpm"]


