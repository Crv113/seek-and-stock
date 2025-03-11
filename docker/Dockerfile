FROM php:8.2-fpm-alpine

# Installer les extensions PHP et utilitaires
RUN apk update && apk add --no-cache \
    zip unzip git curl bash \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && apk add --no-cache --virtual .build-deps gcc g++ make autoconf \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier le code Laravel
COPY . .

# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande de démarrage
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html || true
CMD ["php-fpm"]
