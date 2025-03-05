#!/bin/bash

# Install Composer dependencies
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install PHP dependencies
composer install --no-dev --prefer-dist --optimize-autoloader

# Run migrations (if needed)
php artisan migrate --force
