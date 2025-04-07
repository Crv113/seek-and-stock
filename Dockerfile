FROM php:8.2-fpm

# Dépendances système
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail
WORKDIR /var/www/html

# Étape 1 : copie artisan + bootstrap/cache + vendor placeholders
COPY artisan .
COPY composer.json composer.lock ./
COPY bootstrap ./bootstrap
COPY config ./config
COPY routes ./routes

# Étape 2 : installer les dépendances
RUN composer install --no-dev --optimize-autoloader

# Étape 3 : copier le reste du projet
COPY . .

# Script wait-db
COPY wait-db.sh /usr/local/bin/wait-db.sh
RUN chmod +x /usr/local/bin/wait-db.sh

# Permissions Laravel
RUN git config --global --add safe.directory /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache


