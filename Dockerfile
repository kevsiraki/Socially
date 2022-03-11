FROM php:8.0-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN apt-get update && apt-get upgrade -y
COPY *.php /var/www/html/
COPY *.sql /var/www/html/
COPY *.css /var/www/html/
COPY dont_Trip.conf /etc/apache2/sites-available/
RUN a2ensite dont_Trip.conf
RUN a2dissite 000-default.conf
RUN a2enmod rewrite 

