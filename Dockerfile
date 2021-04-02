FROM php:7.4-alpine
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions xdebug

WORKDIR /app
COPY composer.json composer.lock /app/
RUN composer install
COPY . /app/
CMD ["php", "demo.php"]
