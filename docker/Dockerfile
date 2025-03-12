FROM php:8.2-fpm-alpine

# Installer les extensions PHP et utilitaires
RUN apk update && apk add --no-cache \
    zip unzip git curl bash shadow \
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

# Gestion des droit utilisateur
ARG USER_ID=1000
RUN usermod -u $USER_ID www-data && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R g+w /var/www/html && \
    mkdir -p /run && chown -R www-data:www-data /run && \
    echo "umask 002" >> /etc/profile && \
    echo "umask 002" >> /var/www/.bashrc

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

COPY phpfpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

USER www-data
# Commande de démarrage
CMD ["php-fpm"]
