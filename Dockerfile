FROM php:7.1-apache
MAINTAINER Jan Drábek <jan@drabek.cz>

# Enable various PHP extensions
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli opcache gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable XDebug
RUN pecl install xdebug-2.5.0 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=docker.for.mac.localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install self-signed SSL certificate and enable SSL in Apache
RUN mkdir -p /etc/ssl/localcerts \
    && openssl req -new -x509 -days 365 -nodes -out /etc/ssl/localcerts/apache.pem -keyout /etc/ssl/localcerts/apache.key -subj "/C=CZ/O=Instruktoři Brno, z. s./OU=TMOU/CN=tmou.test" \
    && chmod 600 /etc/ssl/localcerts/apache* \
    && sed -i "s#/etc/ssl/certs/ssl-cert-snakeoil.pem#/etc/ssl/localcerts/apache.pem#g" /etc/apache2/sites-available/default-ssl.conf \
    && sed -i "s#/etc/ssl/private/ssl-cert-snakeoil.key#/etc/ssl/localcerts/apache.key#g" /etc/apache2/sites-available/default-ssl.conf \
    && a2enmod ssl rewrite && a2ensite default-ssl

# Move Apache document root
RUN sed -ri -e 's!/var/www/html!/var/www/html/www!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/www!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir /usr/local/bin \
    && php -r "unlink('composer-setup.php');" \
    && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
