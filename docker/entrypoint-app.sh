#!/bin/sh
# Entrypoint kontainer `app`. Dijalankan sebagai root agar bisa menyiapkan
# volume uploads-data (milik root saat pertama dibuat), lalu php-fpm sendiri
# menurunkan worker ke www-data.
set -e

mkdir -p storage/app/public storage/framework/sessions storage/framework/views \
    storage/framework/cache bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Sengaja TANPA config:cache — env (Dokploy/dashboard atau --env-file)
# harus dibaca live setiap boot, bukan dibekukan saat build.
exec "$@"
