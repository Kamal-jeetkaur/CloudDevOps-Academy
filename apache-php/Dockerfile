FROM php:8.1-apache

# Install PDO + MySQL drivers
RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

# Ensure Apache can read the mounted site
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80