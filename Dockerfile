FROM php:8.2

RUN apt-get update -y && apt-get install -y openssl zip unzip git libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && rm -rf /var/cache/apt/archives

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

EXPOSE 8000