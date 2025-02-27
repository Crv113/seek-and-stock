FROM php:8.2-fpm-alpine

# Installer les extensions PHP et Composer
RUN apk update && apk add --no-cache \
    zip unzip git curl bash \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && apk add --no-cache --virtual .build-deps gcc g++ make autoconf \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier uniquement les fichiers nécessaires pour Composer
COPY composer.json composer.lock ./

# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html --ignore-platform-reqs || true

# Copier le reste du projet
COPY . .

# Corriger les permissions après la copie des fichiers
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exécuter Laravel storage:link
USER www-data
RUN php artisan storage:link || true

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

# Démarrer PHP-FPM
CMD ["php-fpm"]
