FROM fedot/php:7.1.1-alpine
RUN apk add --no-cache \
    libevent-dev \
    autoconf \
    build-base \
    && pecl install -f ev \
    && docker-php-ext-enable ev \
    && apk del \
    libevent-dev \
    autoconf \
    build-base

## uncomment for debug
#RUN php /usr/share/php7/peclcmd.php install -f xdebug && \
#    echo "zend_extension=xdebug.so" > /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_enable=1" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_host=188.134.65.253" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.remote_autostart=1" >> /etc/php7/conf.d/99-xdebug.ini && \
#    echo "xdebug.idekey=phpstorm" >> /etc/php7/conf.d/99-xdebug.ini
#ENV PHP_IDE_CONFIG="serverName=new-backlog"

WORKDIR /application/

EXPOSE 80 443
CMD php -d zend.assertions=1 bin/aerys -c app/aerys-prod.php -d

COPY . /application/
