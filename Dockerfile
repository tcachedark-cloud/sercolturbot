FROM php:8.2-fpm

# 1️⃣ Instalar Nginx
RUN apt-get update && apt-get install -y nginx && rm -rf /var/lib/apt/lists/*

# 2️⃣ Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 3️⃣ Copiar proyecto
COPY . /var/www/html

WORKDIR /var/www/html

# 4️⃣ Permisos
RUN chown -R www-data:www-data /var/www/html

# 5️⃣ Configurar Nginx para puerto 8080
RUN sed -i 's/listen 80/listen 8080/' /etc/nginx/sites-available/default && \
    sed -i 's/fastcgi_pass unix/fastcgi_pass 127.0.0.1:9000/' /etc/nginx/sites-available/default

EXPOSE 8080

# 6️⃣ Iniciar PHP-FPM y Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
