FROM php:7.4-alpine
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions xdebug

WORKDIR /app
COPY . /app/
CMD ["php", "demo.php"]
