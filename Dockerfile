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

# php config
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# nginx config
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# nginx potrzebuje katalogu runtime
RUN mkdir -p /run/nginx

# port dla Render
EXPOSE 80

# start nginx + php
CMD php-fpm -D && nginx -g "daemon off;"