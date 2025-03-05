#!/usr/bin/env bash
# Install PHP and Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Laravel dependencies
composer install --no-dev --optimize-autoloader

# Run migrations (only if your app requires a database)
php artisan migrate --force
