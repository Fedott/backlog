FROM alpine:latest
RUN apk upgrade -U && \
    apk --update --repository=http://dl-4.alpinelinux.org/alpine/edge/testing add \
    curl \
    git \
    openssl \
    libevent libevent-dev \
    build-base \
    autoconf \ 
    php7 \
    php7-xml \
    php7-dom \
    php7-posix \
    php7-mcrypt \
    php7-curl \
    php7-json \
    php7-phar \
    php7-openssl \
    php7-ctype \
    php7-opcache \
    php7-pear \
    php7-dev \
    php7-sockets \
    php7-mbstring

RUN ln -s /etc/php7 /etc/php && \
    ln -s /usr/bin/php7 /usr/bin/php && \
    ln -s /usr/sbin/php-fpm7 /usr/bin/php-fpm && \
    ln -s /usr/lib/php7 /usr/lib/php

RUN php /usr/share/php7/peclcmd.php install -f ev && echo "extension=ev.so" > /etc/php7/conf.d/02-ev.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN git clone -b dev-react-amphp https://05f4fcdedc6fd1dab8bd7268a627d0bd667ca2d0:x-oauth-basic@github.com/fedott/backlog.git /application/

RUN apk del build-base libevent-dev php7-dev autoconf

WORKDIR /application/amphp/

RUN /usr/local/bin/composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader
EXPOSE 80

CMD php bin/aerys -c app/aerys-prod.php

