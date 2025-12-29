FROM php:8.2-apache

# Desactivar MPMs que causan conflicto
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copiar proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Puerto Railway
EXPOSE 8080
