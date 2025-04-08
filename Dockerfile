FROM php:8.2-fpm

ARG ENV=local
ENV APP_ENV=$ENV

# Dépendances système
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail
WORKDIR /var/www/html

# Copie des fichiers
COPY . /var/www/html





# Script wait-db
COPY /wait-db.sh /usr/local/bin/wait-db.sh
RUN chmod +x /usr/local/bin/wait-db.sh

# Artisan / composer
RUN if [ "$APP_ENV" = "prod" ]; then \
      git config --global --add safe.directory /var/www/html && \
      chown -R www-data:www-data /var/www/html && \
      chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
      composer install --no-dev --optimize-autoloader; \
    else \
      composer install && \
      chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache; \
    fi
