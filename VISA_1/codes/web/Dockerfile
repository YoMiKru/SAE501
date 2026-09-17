FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y libsqlite3-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_sqlite gd \
    && rm -rf /var/lib/apt/lists/*

EXPOSE 80