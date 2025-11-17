FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
    && docker-php-ext-install pdo_mysql mysqli zip

RUN a2enmod rewrite

COPY php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

