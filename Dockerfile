#
# Build the app
#
FROM php:7.4-apache

RUN apt-get update && apt-get install -yqq unzip libzip-dev \
    && docker-php-ext-install pdo_mysql opcache zip

# In addition to telling docker to expose the port, you must also configure apache to listen to 8080. These are two versions:

# /etc/apache2/ports.conf
# /etc/apache2/sites-enabled/000-default.conf

RUN sed -si 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
RUN sed -si 's/VirtualHost .:80/VirtualHost *:8080/' /etc/apache2/sites-enabled/000-default.conf

# relax permissions on status
COPY status.conf /etc/apache2/mods-available/status.conf
# Enable Apache mod_rewrite and status
RUN a2enmod rewrite && a2enmod status

WORKDIR /var/www/html

COPY . /var/www/html

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install

# This is important. Symfony needs write permissions and we
# dont know the context in which the container will run, i.e.
# which user will be forced from the outside so better play
# safe for this simple demo.
RUN rm -Rf /var/www/var/*
RUN chown -R www-data /var/www
RUN chmod -R 777 /var/www
