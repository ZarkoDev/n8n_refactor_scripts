FROM php:8.3-fpm

# Configure user/group IDs to match host (override via build args)
ARG PUID=1000
ARG PGID=1000

# Install system dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev \
       libicu-dev libpq-dev default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Set recommended PHP.ini settings
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/99-custom.ini

# Create app user matching host and set ownership
RUN groupadd -g ${PGID} www \
    && useradd -u ${PUID} -g ${PGID} -m www \
    && chown -R www:www /var/www/html

# Run php-fpm workers as the created user
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

CMD ["php-fpm"]
