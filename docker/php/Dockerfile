FROM php:7.4-apache

# Extensions systeme necessaires a la compilation des extensions PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libcurl4-openssl-dev \
        locales \
    && rm -rf /var/lib/apt/lists/*

# Locale francaise (utilisee par setlocale(LC_TIME, "fr_FR") dans fonctions.inc.php)
RUN sed -i 's/^# *\(fr_FR.UTF-8 UTF-8\)/\1/' /etc/locale.gen \
    && locale-gen

# Extensions PHP utilisees par le site
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mysqli \
        gd \
        exif \
        zip \
        curl \
        mbstring

# mod_rewrite pour les .htaccess (tile_proxy, cache de tuiles)
RUN a2enmod rewrite

# Configuration PHP alignee sur la production
COPY php.ini /usr/local/etc/php/conf.d/zz-invader-mapper.ini

# Le site est servi depuis /var/www/html, comme en production
WORKDIR /var/www/html
