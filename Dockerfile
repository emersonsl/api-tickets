FROM php:8.2

# Install packages PHP
RUN apt-get update -y && apt-get install -y openssl zip unzip git libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/cache/apt/archives 

# Install and configure Xdebug
RUN pecl install xdebug \
    && echo 'zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20220829/xdebug.so' | tee /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_port=9000" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=0" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=docker" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=coverage,debug" | tee -a /usr/local/etc/php/conf.d/xdebug.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

EXPOSE 8000