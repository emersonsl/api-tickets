FROM php:8.3.3RC1-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV SERVER_NAME=localhost

RUN echo 'ServerName ${SERVER_NAME}' >> /etc/apache2/apache2.conf

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup --gid 1000 emerson
RUN useradd -u 1000 -g 1000 -Mr emerson

USER emerson