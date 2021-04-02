FROM php:7.4-alpine
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json composer.lock /app/
RUN composer install
COPY . /app/
CMD ["php", "demo.php"]
