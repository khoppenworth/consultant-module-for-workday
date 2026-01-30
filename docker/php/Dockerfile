FROM php:8.2-apache

RUN a2enmod rewrite headers

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Optional: for password hashing & random
RUN php -v

WORKDIR /var/www/html

# Apache config: allow .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
