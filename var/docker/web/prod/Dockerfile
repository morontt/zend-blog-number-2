FROM php:5.6.30-apache

MAINTAINER Alexander Harchenko <morontt@gmail.com>

VOLUME ["/var/www/html"]

ARG HOST_UID=1000

ENV DEBIAN_FRONTEND noninteractive

RUN echo Europe/Kiev > /etc/timezone && \
    dpkg-reconfigure -f noninteractive tzdata

RUN apt-get update && apt-get install -y apt-utils && apt-get install -y \
    mysql-client

RUN docker-php-ext-install pdo_mysql

COPY ./.bashrc /root/.bashrc
COPY ./php.ini /usr/local/etc/php/
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf
RUN echo "LogFormat \"%a %l %u %t \\\"%r\\\" %>s %O \\\"%{User-Agent}i\\\"\" mainlog" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite remoteip && a2dismod deflate -f

RUN usermod -u ${HOST_UID} www-data && groupmod -g ${HOST_UID} www-data

EXPOSE 80
