FROM php:8.2-fpm-alpine

RUN apk update && apk add \
    git \
    curl \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    g++ \
    make \
    freetype-dev \
    libjpeg-turbo-dev

RUN docker-php-ext-install pdo pdo_mysql opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html


USER www-data