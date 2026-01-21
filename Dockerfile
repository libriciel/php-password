FROM hubdocker.libriciel.fr/php:8.3.22-fpm-alpine3.22

ARG UID=10001
ARG GID=10001

WORKDIR /app

RUN addgroup -S appgroup -g $GID && adduser -S appuser -G appgroup -u $UID
RUN mkdir -p /app && chown appuser:appgroup /app

RUN <<EOF
apk add bash bash-completion git php83-pecl-pcov php83-xdebug
cp /usr/lib/php83/modules/pcov.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
cp /usr/lib/php83/modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
docker-php-ext-enable pcov xdebug
EOF

COPY --from=hubdocker.libriciel.fr/composer:2.9.3 /usr/bin/composer /usr/bin/composer

USER appuser

COPY composer.* /app/
RUN composer install

COPY . /app
