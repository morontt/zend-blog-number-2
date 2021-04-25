FROM php:7.1-apache-stretch

MAINTAINER Alexander Harchenko <morontt@gmail.com>

VOLUME ["/var/www/html"]

ARG HOST_UID=1000

ENV DEBIAN_FRONTEND noninteractive
ENV TZ "Europe/Kiev"

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y apt-utils && apt-get install -y \
    mysql-client

RUN docker-php-ext-install -j$(nproc) pdo_mysql opcache

RUN yes | pecl install xdebug-2.9.8 \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" \
    > /usr/local/etc/php/conf.d/xdebug.ini

COPY ./.bashrc /root/.bashrc
COPY ./php.ini /usr/local/etc/php/
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite remoteip && a2dismod deflate -f

RUN usermod -u ${HOST_UID} www-data && groupmod -g ${HOST_UID} www-data

EXPOSE 80
