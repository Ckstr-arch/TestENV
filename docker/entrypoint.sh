#!/bin/sh
set -eu
cd /var/www/html
mkdir -p var/sessions
php bin/container-init.php
chown -R www-data:www-data var
chmod 700 var/sessions
exec docker-php-entrypoint "$@"
