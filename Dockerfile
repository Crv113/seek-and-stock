FROM php:8.2-fpm

# Argument pour déterminer l’environnement (local ou prod)
ARG ENV=local

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer depuis une image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail
WORKDIR /var/www/html

# Copier les fichiers applicatifs
COPY . .

# Copier le script d’attente de la DB
COPY /wait-db.sh /usr/local/bin/wait-db.sh
RUN chmod +x /usr/local/bin/wait-db.sh

# Installer les dépendances PHP + cache si prod
RUN if [ "$ENV" = "prod" ]; then \
      chown -R www-data:www-data /var/www/html && \
      chmod -R 775 storage bootstrap/cache && \
      composer install --no-dev --optimize-autoloader && \
    else \
      composer install && \
      chmod -R 777 storage bootstrap/cache; \
    fi
