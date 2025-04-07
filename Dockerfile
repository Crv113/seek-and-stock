FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# ⏱ Copy only composer files first to optimize Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy remaining project files
COPY . .

# Add wait-db script
COPY wait-db.sh /usr/local/bin/wait-db.sh
RUN chmod +x /usr/local/bin/wait-db.sh

# Set proper permissions for Laravel
RUN git config --global --add safe.directory /var/www/html && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

