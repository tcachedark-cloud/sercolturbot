FROM php:8.2-apache

# 🔥 Desactivar MPMs conflictivos (NO prefork)
RUN a2dismod mpm_event mpm_worker || true

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache
RUN a2enmod rewrite

# Copiar SOLO el código público
COPY public/ /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Puerto Railway
EXPOSE 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
