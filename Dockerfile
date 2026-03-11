FROM php:8.3-fpm-alpine

# instalacja nginx i bibliotek
RUN apk add --no-cache \
    nginx \
    zlib \
    libzip \
    libpng \
    libjpeg-turbo \
    postgresql-libs \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    postgresql-dev

# instalacja rozszerzeń php
RUN docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
    opcache \
    zip \
    gd \
    bcmath \
    pgsql \
    pdo_pgsql

# katalog aplikacji
WORKDIR /app

# kopiowanie kodu
COPY . /app

# konfiguracja PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# konfiguracja nginx
COPY docker/nginx/nginx.conf /etc/nginx/http.d/default.conf

# nginx runtime
RUN mkdir -p /run/nginx

# port dla Render
EXPOSE 80

# start php + nginx
CMD php-fpm -D && nginx -g "daemon off;"