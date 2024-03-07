FROM php:8.2

# Install packages PHP
RUN apt-get update -y && apt-get install -y openssl zip unzip git libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/cache/apt/archives 

# Install and configure Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.start_with_request=yes" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=0" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=coverage,develop" | tee -a /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" | tee -a /usr/local/etc/php/conf.d/xdebug.ini 
    
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

EXPOSE 8000