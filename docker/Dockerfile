FROM php:8.2-fpm-alpine

# Installer les extensions PHP et utilitaires
RUN apk update && apk add --no-cache \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && apk add --no-cache --virtual .build-deps gcc g++ make autoconf \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Copier le code Laravel
WORKDIR /var/www/html
COPY . .

# Installer Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html


# Définir les permissions pour www-data sur les dossiers nécessaires
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN php artisan storage:link

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Démarrer PHP-FPM
CMD ["php-fpm"]
