#!/bin/sh

echo "Checking storage permissions..."
CURRENT_UID=$(stat -c "%u" /var/www/html/storage)
CURRENT_GID=$(stat -c "%g" /var/www/html/storage)

if [ "$CURRENT_UID" != "33" ] || [ "$CURRENT_GID" != "33" ]; then
    echo "Fixing permissions for storage..."
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
else
    echo "Permissions are already correct."
fi

# Démarrer PHP-FPM après avoir appliqué les permissions
exec "$@"
