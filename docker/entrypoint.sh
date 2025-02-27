#!/bin/sh

echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
echo "Starting PHP-FPM..."
# Exécuter le processus principal (PHP-FPM)
exec "$@"
