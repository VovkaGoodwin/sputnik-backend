FROM composer:2.7.2 AS composer
FROM php:8.3-cli AS php

LABEL authors="VovkaGoodwin"

ARG UID=20
ARG GID=501

RUN apt-get update \
   && apt-get install -y \
   git zip unzip libpq-dev curl vim \
   && addgroup --gid $GID app \
   && adduser --uid $UID --gid $GID --gecos 'app' app \
   && echo "app ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers \
   && mkdir /app \
   && chown app:app -R /app

RUN docker-php-ext-install sockets pgsql pdo_pgsql

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=ghcr.io/roadrunner-server/roadrunner:2023.1.1 /usr/bin/rr /usr/bin/rr

WORKDIR /app
USER app

COPY --chown=app:app composer.* .

RUN composer install --optimize-autoloader --no-scripts

COPY --chown=app:app . .

RUN composer run-script post-autoload-dump
RUN composer run-script post-update-cmd

COPY --chown=app:app --chmod=777 docker-entrypoint.sh docker-entrypoint.sh
COPY --chown=app:app .rr.development.yaml .rr.yaml

ENTRYPOINT ["./docker-entrypoint.sh"]
