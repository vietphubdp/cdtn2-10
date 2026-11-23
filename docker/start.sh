#!/bin/sh
set -eu

key_file=/var/www/html/storage/app/private/docker-app-key
if [ -z "${APP_KEY:-}" ]; then
    if [ ! -s "$key_file" ]; then
        (umask 077; php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$key_file.tmp")
        mv "$key_file.tmp" "$key_file"
    fi
    export APP_KEY="$(cat "$key_file")"
fi

php artisan migrate --force
exec apache2-foreground
