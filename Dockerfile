# FROM hubdocker.libriciel.fr/php:8.3.22-fpm-alpine3.22
FROM hubdocker.libriciel.fr/php:8.3.19-fpm-alpine3.20

WORKDIR /app


RUN <<EOF
apk add bash bash-completion git php83-pecl-pcov php83-xdebug
cp /usr/lib/php83/modules/pcov.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
cp /usr/lib/php83/modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
docker-php-ext-enable pcov xdebug
EOF

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.* /app/
RUN composer install

COPY . /app
