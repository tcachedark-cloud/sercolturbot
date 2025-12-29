FROM php:8.2-apache

# 1️⃣ Detener Apache antes de hacer cambios
RUN service apache2 stop || true

# 2️⃣ Eliminar TODOS los MPM habilitados
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

# 3️⃣ Habilitar SOLO prefork de mods-available
RUN ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load && \
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

# 4️⃣ Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 5️⃣ Apache config básica
RUN a2enmod rewrite

# 6️⃣ Copiar proyecto
COPY . /var/www/html

WORKDIR /var/www/html

# 7️⃣ Permisos
RUN chown -R www-data:www-data /var/www/html

# 8️⃣ Puerto Railway
EXPOSE 8080

# 9️⃣ Apache escucha en 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
