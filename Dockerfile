FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80