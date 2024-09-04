FROM php:8.3-fpm-alpine

RUN apk update && apk upgrade


RUN apk add libpq-dev
RUN apk add icu-dev

RUN docker-php-ext-configure intl

RUN docker-php-ext-install pdo pdo_pgsql pgsql intl

RUN apk add --update linux-headers --no-cache autoconf g++ make \
    && pecl install xdebug \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug



RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY ./dev/php/conf.d /usr/local/etc/php/conf.d
COPY ./dev/php-fpm/conf.d /usr/local/etc/php-fpm.d
COPY ./dev/php-fpm/conf.d /usr/local/etc/php/conf.d

WORKDIR /app

