FROM ubuntu:22.04

WORKDIR /app

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y  --no-install-recommends \
        ca-certificates \
        make \
        php-cli  \
        php-curl \
        php-dom \
        php-mbstring \
        php-pcov \
        php-xdebug \
        unzip \
        && rm -r /var/lib/apt/lists/*


RUN phpenmod pcov xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.* /app/
RUN composer install

COPY . /app
