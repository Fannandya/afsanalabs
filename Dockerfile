# Multi-stage, multi-arch (jangan pasang `platform:` — biarkan builder memilih
# varian native; untuk deploy beda arch pakai buildx, lihat Makefile target build-amd64).
# Target: `app` (PHP-FPM Laravel) dan `web` (nginx + hasil build SvelteKit).

# ---------- frontend (SvelteKit SPA statis) ----------
FROM node:22-alpine AS frontend-build
WORKDIR /app/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY frontend/ ./
# Kosongkan = API relatif ke domain yang sama (deploy satu domain/Dokploy).
# Isi bila API beda origin, cth. http://localhost:8000/api/v1 (dev).
ARG VITE_API_BASE=
ENV VITE_API_BASE=$VITE_API_BASE
RUN npm run build

# ---------- dependensi PHP ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress
COPY backend/ ./
# Buang cache lokal (packages/config/routes) agar discover + boot baca env live.
RUN rm -f bootstrap/cache/*.php \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative

# ---------- app (PHP-FPM Laravel) ----------
FROM php:8.3-fpm-alpine AS app
RUN apk add --no-cache bash \
    && docker-php-ext-install -j$(nproc) pdo_mysql bcmath opcache pcntl
WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app /var/www/html
RUN mkdir -p storage/app/public storage/framework/sessions storage/framework/views \
    storage/framework/cache bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
# Catatan: .env TIDAK dibakar ke image — semua config dibaca live dari environment
# (Dokploy: isi via menu Environment; lokal: --env-file .env.docker).
COPY docker/entrypoint-app.sh /usr/local/bin/entrypoint-app.sh
RUN chmod +x /usr/local/bin/entrypoint-app.sh
EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD php artisan tinker --execute='DB::connection()->getPdo();' > /dev/null 2>&1 || exit 1
ENTRYPOINT ["entrypoint-app.sh"]
CMD ["php-fpm"]

# ---------- web (nginx + file statis) ----------
FROM nginx:alpine AS web
COPY nginx/prod.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend-build /app/frontend/build /usr/share/nginx/html
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -qO- http://127.0.0.1/health > /dev/null 2>&1 || exit 1
