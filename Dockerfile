ARG BASE_IMAGE=php:8.2-fpm
FROM ${BASE_IMAGE}

ARG ENV=local
ENV APP_ENV=$ENV

# Dossier de travail
WORKDIR /var/www/html

# Copie des fichiers
COPY . /var/www/html

# Script wait-db
COPY /wait-db.sh /usr/local/bin/wait-db.sh
RUN chmod +x /usr/local/bin/wait-db.sh

# Artisan / composer

RUN git config --global --add safe.directory /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && \
    if [ "$APP_ENV" = "prod" ] || [ "$APP_ENV" = "dev" ]; then \
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev; \
    else \
        composer install --no-interaction --prefer-dist; \
    fi
