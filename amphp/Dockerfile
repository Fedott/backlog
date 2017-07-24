FROM php:7-alpine

RUN apk add --no-cache \
    libevent-dev \
    autoconf \
    build-base \
    && pecl install -f ev \
    && docker-php-ext-enable ev \
    && apk del \
    libevent-dev \
    autoconf \
    build-base \
    && echo 'grep -c ^processor /proc/cpuinfo 2>/dev/null || 1' > /usr/bin/nproc && chmod +x /usr/bin/nproc

WORKDIR /application/

EXPOSE 80 443 8080

CMD php -d zend.assertions=-1 bin/aerys -c app/aerys-prod.php

COPY . /application/
