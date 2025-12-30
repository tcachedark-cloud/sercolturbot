FROM php:8.2-apache

# Activar mod_rewrite
RUN a2enmod rewrite

# Instalar extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Cambiar DocumentRoot con la configuración adecuada
RUN sed -i 's|^DocumentRoot /var/www/html$|DocumentRoot /var/www/html/public|' \
    /etc/apache2/sites-available/000-default.conf

# Copiar proyecto
COPY . /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
