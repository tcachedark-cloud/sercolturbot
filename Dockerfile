FROM php:8.2-apache

# 1️⃣ Eliminar TODOS los archivos de MPM (más efectivo que a2dismod)
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

# 2️⃣ Activar SOLO prefork
RUN a2enmod mpm_prefork

# 3️⃣ Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 4️⃣ Apache config básica
RUN a2enmod rewrite

# 5️⃣ Copiar proyecto
COPY . /var/www/html

WORKDIR /var/www/html

# 6️⃣ Permisos
RUN chown -R www-data:www-data /var/www/html

# 7️⃣ Puerto Railway
EXPOSE 8080

# 8️⃣ Apache escucha en 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
