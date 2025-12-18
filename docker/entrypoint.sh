#!/bin/sh

set -e

echo "Starting Neo ERP application..."

# Create .env file from environment variables
echo "Creating .env file from environment variables..."
cat > /var/www/html/.env << EOF
APP_NAME="${APP_NAME:-Neo ERP}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}
CACHE_DRIVER=${CACHE_DRIVER:-redis}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
SESSION_DRIVER=${SESSION_DRIVER:-redis}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}

REDIS_HOST=${REDIS_HOST:-redis}
REDIS_PASSWORD=
REDIS_PORT=${REDIS_PORT:-6379}
EOF

chown www-data:www-data /var/www/html/.env
chmod 644 /var/www/html/.env

echo ".env file created successfully"

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is up - executing migrations"

# Run migrations
php artisan migrate --force --no-interaction

# Clear and cache config
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage directories exist with proper structure
echo "Setting up storage directories..."
mkdir -p /var/www/html/storage/app/public/logos
mkdir -p /var/www/html/storage/app/public/documents
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/storage/logs

# Create storage symlink if needed
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link
fi

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Ensure public/storage symlink has correct permissions
if [ -L /var/www/html/public/storage ]; then
    chown -h www-data:www-data /var/www/html/public/storage
fi

echo "Application ready!"

# Execute the main command
exec "$@"
