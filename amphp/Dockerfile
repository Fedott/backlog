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

## uncomment for debug
#RUN php /usr/share/php7/peclcmd.php install -f xdebug && \
#    echo "zend_extension=xdebug.so" > /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_enable=1" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_host=188.134.65.253" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_autostart=1" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.idekey=phpstorm" >> /etc/php7/conf.d/99-xdebug.ini
#ENV PHP_IDE_CONFIG="serverName=new-backlog"

RUN apk del build-base libevent-dev php7-dev autoconf

WORKDIR /application/

EXPOSE 80 443
CMD php -d zend.assertions=1 bin/aerys -c app/aerys-prod.php -d

COPY . /application/
