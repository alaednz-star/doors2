FROM php:8.3-apache

# PDO MySQL for the app's database layer
RUN docker-php-ext-install pdo pdo_mysql

# Apache modules the app's .htaccess relies on
RUN a2enmod rewrite headers

# App code
WORKDIR /var/www/html
COPY . /var/www/html

# Writable upload/storage dirs (app expects these writable, like 0777 locally)
RUN chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage 2>/dev/null || true \
    && chmod -R 0775 /var/www/html/public/uploads /var/www/html/storage 2>/dev/null || true

# Serve the app under /door-showroom (routes/assets are hardcoded to this path)
# and redirect the bare domain root to it.
COPY docker/app.conf /etc/apache2/conf-enabled/app.conf

# Apache listens on 8080; set Railway's PORT variable to 8080 to match.
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8080
CMD ["apache2-foreground"]
