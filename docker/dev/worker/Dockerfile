FROM php:8.3-cli-alpine

RUN apk update && apk upgrade
RUN apk add libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN apk add libevent-dev

###RUN apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS

RUN apk add --update linux-headers --no-cache autoconf g++ make
RUN docker-php-ext-install sockets pcntl posix
RUN pecl install event


RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
RUN echo "extension=event.so" > /usr/local/etc/php/conf.d/event.ini
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet


WORKDIR /app

CMD ["php", "worker.php", "start"]

