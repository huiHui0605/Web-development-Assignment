FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql
COPY VolunteerSystem/ /var/www/html/
RUN find /var/www/html -type d -exec chmod 755 {} + && find /var/www/html -type f -exec chmod 644 {} +
