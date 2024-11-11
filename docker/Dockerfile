FROM php:8.2-fpm-alpine

# Installer Nginx, extensions PHP et utilitaires
RUN apk update && apk add --no-cache nginx \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && apk add --no-cache --virtual .build-deps gcc g++ make autoconf \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copier le code Laravel
WORKDIR /var/www/html
COPY . .
# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# Exposer le port 80
EXPOSE 80

# Démarrer PHP-FPM et Nginx ensemble
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
