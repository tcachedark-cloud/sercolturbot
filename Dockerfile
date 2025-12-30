FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Cambiar DocumentRoot SOLO en 000-default.conf
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Copiar proyecto
COPY . /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
