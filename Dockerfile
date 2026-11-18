# Stage 1: Composer production dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# Stage 2: Frontend assets (Vite)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build


# Stage 3: Final runtime image
FROM dunglas/frankenphp:1.13.5-php8-alpine

ARG RELEASE_VERSION

# Installer les extensions PHP requises par Laravel (ex: MySQL, Redis, etc.)
RUN install-php-extensions \
    pdo_mysql \
    zip \
    opcache \
    intl \
    pcntl

# COPYING SOURCE CODE
COPY --from=vendor /app /app
COPY --from=assets /app/public/build ./public/build
WORKDIR /app

# SAVING THE RELEASE VERSION
RUN echo $RELEASE_VERSION > RELEASE.txt

# INSTALLING THE CRONTAB
RUN { echo "* * * * * php /app/artisan schedule:run >> /dev/null 2>&1"; } | crontab -

# SETUP OF FILESHARING
RUN cp .docker/.env .
RUN php artisan key:generate --force
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan orbit:clear

# EXPOSING VOLUME
VOLUME /app/storage

RUN chown -R www-data:www-data /app /data

USER www-data

# Définir le nom du serveur (ex: :80 pour HTTP seul)
ENV SERVER_NAME=:80

# Exposer le port HTTP (et HTTPS/443 si nécessaire)
EXPOSE 80
