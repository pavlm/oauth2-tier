FROM php:8.1 AS base

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt update && \
    apt install -y --no-install-recommends git-all zip

RUN docker-php-ext-install pcntl

RUN install-php-extensions zip xdebug

ENV XDEBUG_CLI="-d xdebug.mode=debug -d xdebug.start_with_request"

RUN useradd -m php --uid 1000

WORKDIR /app


FROM base AS app

ADD composer.* /app/
ADD bin /app/bin/

RUN composer install -vv --prefer-dist
RUN composer run-script patch-vendor || true

ADD ./ /app/

USER 1000:1000
