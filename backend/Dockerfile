# Remindo backend — Laravel 12 on PHP 8.3 (FrankenPHP-free, simple built-in server for dev/CI).
FROM php:8.3-cli-alpine

RUN apk add --no-cache \
        postgresql-dev libpng-dev libzip-dev icu-dev oniguruma-dev linux-headers $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip intl bcmath pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
