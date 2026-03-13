FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo_pgsql pgsql

WORKDIR /app

COPY . .

CMD php -S 0.0.0.0:10000 -t public