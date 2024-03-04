FROM php:8.3-apache
RUN a2enmod rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql
WORKDIR /var/www/html
COPY ./ .
LABEL authors="yura"