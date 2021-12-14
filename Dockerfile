FROM php:7.3-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql



WORKDIR /var/www
COPY ./src/ /var/www/html/
RUN chown -R www-data:www-data /var/www
