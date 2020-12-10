FROM php:7.4-cli

RUN apt-get update -qq \
  && apt-get install -qq --no-install-recommends git zip unzip \
  && apt-get clean

RUN apt-get install -y libz-dev libmemcached-dev && \
    pecl install memcached && \
    docker-php-ext-enable memcached

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupmod -g 1000 www-data \
  && usermod -u 1000 -g www-data www-data \
  && chown -hR www-data:www-data /var/www /usr/local/src

USER www-data:www-data
WORKDIR /usr/local/src/app

ENV PATH=$PATH:/var/www/.composer/vendor/bin

CMD ["php", "./bin/circuit-breaker"]
