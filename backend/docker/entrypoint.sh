#!/bin/bash

# Wait for database to be ready
echo "Waiting for database..."
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    sleep 1
done
echo "Database is ready!"

# Run migrations
php artisan migrate --force

# Seed database (only if tables are empty)
php artisan db:seed --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
php-fpm
