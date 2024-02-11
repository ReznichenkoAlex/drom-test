FROM php:8.2-alpine3.19

RUN apk add --update --no-cache shadow

RUN wget -O /usr/local/bin/install-php-extensions "https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions" \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions xdebug \
    && docker-php-ext-enable xdebug

ARG UID
RUN usermod -u $UID www-data && chown -R www-data:www-data /var/www

COPY ./conf.d /usr/local/etc/php/conf.d
COPY --from=composer:2.6.6 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/app

USER www-data:www-data
