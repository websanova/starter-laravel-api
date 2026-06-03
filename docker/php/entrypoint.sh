#!/bin/bash
if [ -d "vendor" ]; then
    php artisan serve --host=0.0.0.0 --port=8000 &
fi
exec php-fpm
