FROM ubuntu:18.04 AS builder

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        make \
        php-cli  \
        php-dev \
        && rm -r /var/lib/apt/lists/*

RUN pecl install pcov

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && \
    mv /usr/local/bin/composer.phar /usr/local/bin/composer && \
    php -r "unlink('composer-setup.php');"

FROM ubuntu:18.04

WORKDIR /app

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y  --no-install-recommends \
        ca-certificates \
        make \
        php-cli  \
        php-curl \
        php-dom \
        php-mbstring \
        php-xdebug \
        unzip \
        && rm -r /var/lib/apt/lists/*

COPY --from=builder /usr/lib/php/20170718/pcov.so  /usr/lib/php/20170718/pcov.so

RUN echo "extension=pcov.so" > /etc/php/7.2/mods-available/pcov.ini && \
    phpenmod pcov xdebug


COPY --from=builder /usr/local/bin/composer /usr/local/bin/composer

COPY composer.* /app/
RUN composer install

COPY . /app
