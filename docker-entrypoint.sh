#!/bin/sh
set -e
php-fpm -F &
nginx -g 'daemon off;'