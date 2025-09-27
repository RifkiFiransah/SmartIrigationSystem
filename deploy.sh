#!/bin/bash

cd /path/to/your/project

echo "Starting deployment..."

# Force clean pull
git reset --hard HEAD
git clean -fd
git pull origin main

# Install dependencies (will generate new composer.lock)
composer install --no-dev --optimize-autoloader

# Build Filament assets
php artisan filament:assets

# Build other assets if using Vite/Mix
npm install
npm run build

# Laravel optimization
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Restart services
php artisan queue:restart

echo "Deployment completed successfully!"