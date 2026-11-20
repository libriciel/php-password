# syntax=docker/dockerfile:1

# Image de CI et de développement : elle ne sert qu'à lancer les tests et les outils
# d'analyse, la bibliothèque est distribuée par Composer.
FROM hubdocker.libriciel.fr/php:8.3.22-fpm-alpine3.22@sha256:13147c0420a956ae3ecab236c0a8ee8073d9bb100be6dde7f23c4c447e59ec8b

WORKDIR /app

RUN <<EOT
apk add --no-cache bash bash-completion git php83-pecl-pcov php83-xdebug
cp /usr/lib/php83/modules/pcov.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
cp /usr/lib/php83/modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
docker-php-ext-enable pcov xdebug
EOT

COPY --from=hubdocker.libriciel.fr/composer:2.11.2@sha256:919efe8e62179c71dfd31fad035fd671fe7454211c6b2aa30a10af0ab4acedba /usr/bin/composer /usr/bin/composer

COPY composer.* /app/

# Le secret composer_auth porte le jeton GitHub (évite la limite de 60 requêtes/h de
# l'API anonyme). Passé en secret BuildKit et non en --build-arg, il n'apparaît ni
# dans l'historique ni dans les couches de l'image. Secret absent ou vide :
# installation en anonyme.
RUN --mount=type=secret,id=composer_auth,target=/tmp/auth.json \
  if [ -s /tmp/auth.json ]; then export COMPOSER_AUTH="$(cat /tmp/auth.json)"; fi; \
  composer install --no-interaction --prefer-dist --no-progress

COPY . /app
