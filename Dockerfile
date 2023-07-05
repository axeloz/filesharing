FROM php:8.2-apache
MAINTAINER axeloz

# INSTALLING SYSTEM DEPENDENCIES
RUN apt-get update -y && apt-get install -y libmcrypt-dev libonig-dev build-essential libxml2-dev libzip-dev gnupg unzip curl wget findutils tar grep nano cron

# INSTALLING PHP DEPENDENCIES
RUN docker-php-ext-install \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
		opcache \
        xml \
		zip

# ADDING VHOST
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# ADDING COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# INSTALLING YARN
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn

# SETTING WORKDIR AND ENV
ENV WEB_DOCUMENT_ROOT /app/public
ENV APP_ENV production
COPY . /app
WORKDIR /app



# INSTALLING THE CRONTAB
RUN { echo "* * * * * php /app/artisan schedule:run >> /dev/null 2>&1"; } | crontab -

# INSTALLING COMPOSER DEPENDENCIES
RUN composer install --no-interaction --optimize-autoloader --no-dev
RUN chown -R www-data:www-data /app

# SETUP OF FILESHARING
RUN cp .docker/.env .
RUN php artisan key:generate --force
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan orbit:clear

# INSTALLING YARN DEPENDENCIES AND BUILDING
RUN yarn
RUN yarn build

# EXPOSING VOLUME
VOLUME /app/storage

# EXPOSING PORT
EXPOSE 80
