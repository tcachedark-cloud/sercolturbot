FROM php:8.2-apache

# 🔥 Apagar TODOS los MPM primero (clave del problema)
RUN a2dismod mpm_event mpm_worker || true

# ✅ Activar SOLO prefork
RUN a2enmod mpm_prefork

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache básico
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Puerto Railway
EXPOSE 8080

# Apache escucha en 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
