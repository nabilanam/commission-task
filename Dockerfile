FROM php:8.2-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

COPY . /usr/src/commission-task
WORKDIR /usr/src/commission-task

RUN apt-get update
RUN apt-get install -y build-essential git curl unzip
RUN apt-get install -y libzip-dev
RUN docker-php-ext-install zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=1024M" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN composer install

CMD bash -c "php $*"
