FROM php:8.2-apache

RUN apt-get update -y && apt-get install -y libmcrypt-dev libonig-dev build-essential libxml2-dev libzip-dev gnupg
RUN docker-php-ext-install \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
		opcache \
        xml \
		zip

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn

ENV WEB_DOCUMENT_ROOT /app/public
ENV APP_ENV production

WORKDIR /app
COPY . /app
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /app && a2enmod rewrite
RUN composer install --no-interaction --optimize-autoloader --no-dev

RUN cp -n .env.example .env
RUN php artisan key:generate
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

RUN yarn
RUN yarn build

VOLUME /app/storage

EXPOSE 80
