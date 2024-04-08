FROM composer:2 as build_composer

WORKDIR /app
COPY . /app
RUN composer install --no-dev --ignore-platform-reqs --no-interaction


FROM node:20 as build_frontend

WORKDIR /app
COPY --from=build_composer /app /app
RUN npm install --no-audit
RUN npm run prod
RUN rm -rf node_modules


FROM ubuntu:22.04

ARG PHP_VERSION=8.3

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND noninteractive
ENV TZ=UTC

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y nano vim cron nginx \
       gnupg gosu curl ca-certificates zip unzip git supervisor libcap2-bin libpng-dev python2 sendmail redis-tools \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && echo "keyserver hkp://keyserver.ubuntu.com:80" >> ~/.gnupg/dirmngr.conf \
    && gpg --recv-key 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c \
    && gpg --export 0x14aa40ec0831756756d7f66c4f4ea0aae5267a6c > /usr/share/keyrings/ppa_ondrej_php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu jammy main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && apt-get update \
    && apt-get install -y php$PHP_VERSION php$PHP_VERSION-cli php$PHP_VERSION-fpm \
       php$PHP_VERSION-gd php$PHP_VERSION-curl php$PHP_VERSION-tokenizer \
       php$PHP_VERSION-mysql php$PHP_VERSION-mbstring \
       php$PHP_VERSION-xml php$PHP_VERSION-zip php$PHP_VERSION-bcmath \
       php$PHP_VERSION-intl php$PHP_VERSION-readline \
       php$PHP_VERSION-msgpack php$PHP_VERSION-igbinary php$PHP_VERSION-redis \
    && apt-get update \
    && apt-get install -y mysql-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# USER
RUN groupadd --force -g 1000 forge
RUN useradd -ms /bin/bash --no-user-group -g 1000 -u 1000 forge
RUN usermod -aG sudo forge
RUN usermod -aG forge www-data

# NGINX
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx_default.conf /etc/nginx/sites-available/default

# PHP
COPY ./docker/php-fpm.conf /etc/php/$PHP_VERSION/fpm/php-fpm.conf
COPY ./docker/www.conf /etc/php/$PHP_VERSION/fpm/pool.d/www.conf
COPY ./docker/php.ini /etc/php/$PHP_VERSION/cli/conf.d/99-php.ini

# CRON
COPY ./docker/app-cron-file /etc/cron.d/app-cron-file
RUN chmod 0644 /etc/cron.d/app-cron-file
RUN crontab /etc/cron.d/app-cron-file

# SERVICES
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PROJECT FILES OWNERSHIP AND PERMISSIONS
RUN chown -R forge:forge /var/www
COPY --from=build_frontend --chown=forge:forge /app /var/www/html

RUN chmod a+x docker-run

EXPOSE 80

ENTRYPOINT [ "./docker-run" ]
