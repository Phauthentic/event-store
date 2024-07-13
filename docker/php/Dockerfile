FROM php:8.3-cli

RUN apt-get update
RUN apt-get install -y libzip-dev zip git

RUN pear update-channels \
    && pecl update-channels \
    && pecl install xdebug redis

RUN docker-php-ext-install pdo_mysql zip
RUN docker-php-ext-enable pdo_mysql zip redis xdebug

RUN echo "xdebug.mode=coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
