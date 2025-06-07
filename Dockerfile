FROM ubuntu:18.04
LABEL maintainer="Muhammad Surya Ihsanuddin<surya.kejawen@gmail.com>"

ENV DEBIAN_FRONTEND noninteractive

RUN sed -i 's/http:\/\/archive.ubuntu.com/http:\/\/kartolo.sby.datautama.net.id/g' /etc/apt/sources.list

# Install Software
RUN apt-get update && apt-get upgrade -y
RUN apt-get install nginx-full supervisor vim -y
RUN apt-get install software-properties-common build-essential -y
RUN apt-get install curl ca-certificates -y
RUN apt-get update
RUN apt-get install php8.3 php8.3-cli php8.3-curl php8.3-intl php8.3-mbstring php8.3-xml php8.3-zip \
    php8.3-bcmath php8.3-cli php8.3-fpm php8.3-imap php8.3-opcache php8.3-apcu php8.3-xmlrpc \
    php8.3-bz2 php8.3-common php8.3-gd php8.3-ldap php8.3-pgsql php8.3-readline php8.3-soap php8.3-tidy php8.3-xsl php-apcu -y
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get remove --purge -y software-properties-common python-software-properties && \
    apt-get autoremove -y && \
    apt-get clean && \
    apt-get autoclean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* ~/.composer

# Setup Environment
ENV NGINX_WEBROOT   /semarthris/public
ENV SYMFONY_ENV     dev
ENV VARNISH_CONFIG  /etc/varnish/default.vcl
ENV CACHE_SIZE      512m
ENV VARNISHD_PARAMS -p default_ttl=3600 -p default_grace=3600
ENV VARNISH_PORT    80
ENV BACKEND_HOST    localhost
ENV BACKEND_PORT    8080

# Supervisor Configuration
ADD docker/supervisor/supervisor.conf /etc/supervisord.conf

# Nginx Configuration
ADD docker/nginx/sites-enabled/site.conf /etc/nginx/conf.d/default.conf
ADD docker/nginx/sites-enabled/php-fpm.conf /etc/nginx/conf.d/php-fpm.conf
ADD docker/nginx/nginx.conf /etc/nginx/nginx.conf
ADD docker/nginx/fastcgi_cache /etc/nginx/fastcgi_cache
ADD docker/nginx/static_files.conf /etc/nginx/static_files.conf
ADD docker/nginx/logs/site.access.log /var/log/nginx/site.access.log
ADD docker/nginx/logs/site.error.log /var/log/nginx/site.error.log
ADD docker/nginx/etc/sysctl.conf /etc/sysctl.conf
ADD docker/nginx/etc/security/limits.conf /etc/security/limits.conf

RUN mkdir -p /tmp/nginx/cache
RUN chmod 777 -R /tmp/nginx

RUN chmod 777 /var/log/nginx/site.access.log
RUN chmod 777 /var/log/nginx/site.error.log

# PHP Configuration
ADD docker/php/php.ini /etc/php/8.3/fpm/php.ini
ADD docker/php/php.ini /etc/php/8.3/cli/php.ini
ADD docker/php/php-fpm.conf /etc/php/8.3/fpm/php-fpm.conf
RUN mkdir /run/php
RUN touch /run/php/php8.3-fpm.sock
RUN chmod 777 /run/php/php8.3-fpm.sock

# Setup Application
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer global require "hirak/prestissimo:~0.3" --prefer-dist --no-progress --no-suggest --optimize-autoloader --classmap-authoritative -vvv \
&& composer clear-cache

WORKDIR /semarthris

COPY composer.json ./
COPY composer.lock ./

RUN mkdir -p \
        var/cache \
        var/logs \
        var/sessions \
    && chmod 777 -R var/ \
    && composer update --prefer-dist --no-autoloader --no-scripts --no-progress --no-suggest -vvv \
    && composer clear-cache

COPY bin bin/
COPY config config/
COPY data data/
COPY public public/
COPY src src/
COPY templates templates/
COPY .env.dist ./.env.dist

RUN composer dump-autoload --optimize --classmap-authoritative

# Here we go
ADD docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 443 80

CMD ["/start.sh"]
