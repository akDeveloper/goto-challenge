FROM php:8.1-apache
WORKDIR "/var/www"

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libzip-dev \
    zip \
    unzip \
    pkg-config \
    libssl-dev \
    && rm -r /var/lib/apt/lists/*;

# Install Xdebug
RUN yes | pecl install xdebug

RUN sed -i 's!ErrorLog ${APACHE_LOG_DIR}/error.log!ErrorLog /dev/stderr!g' /etc/apache2/apache2.conf

RUN a2enmod rewrite

RUN curl -L https://cs.symfony.com/download/php-cs-fixer-v3.phar -o php-cs-fixer \
    && chmod a+x php-cs-fixer \
    && mv php-cs-fixer /usr/bin/php-cs-fixer