FROM php:8.2-fpm-alpine

COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

# Update package list
RUN apk update
RUN apk upgrade -q -U -a

RUN composer self-update
