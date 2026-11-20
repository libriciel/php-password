# syntax=docker/dockerfile:1

# Image de CI et de développement : elle ne sert qu'à lancer les tests et les outils
# d'analyse, la bibliothèque est distribuée par Composer.
# PHP 8.5 et non 8.6 : aucune version publiée de pcov ou de Xdebug ne supporte
# encore PHP 8.6 (sorti le 2026-11-19).
FROM hubdocker.libriciel.fr/php:8.5.13-fpm-alpine3.24@sha256:51414d5291758fe27f58a64c7e8a078465d32cc7dcef7bda803f5b36019802e0

WORKDIR /app

ARG PCOV_VERSION=1.0.12
ARG XDEBUG_VERSION=3.5.3

RUN <<EOT
set -e
apk add --no-cache bash bash-completion git
apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers
pecl install "pcov-${PCOV_VERSION}" "xdebug-${XDEBUG_VERSION}"
docker-php-ext-enable pcov xdebug
apk del .build-deps
rm -rf /tmp/pear
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
