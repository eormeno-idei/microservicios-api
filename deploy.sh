#!/bin/bash
cd /var/www/microservicios-api

echo "Fetching changes..."
git fetch origin

echo "Switching to branch: $1"
git checkout $1
git pull origin $1

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Running migrations..."
php artisan migrate --force

echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Rebuilding cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Fixing permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache

echo "Restarting services..."
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart laravel-worker:*

echo "Deploy complete!"
