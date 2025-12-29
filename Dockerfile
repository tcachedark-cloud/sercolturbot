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

# 5️⃣ Crear configuración personalizada de Nginx
RUN mkdir -p /etc/nginx/sites-enabled && \
    echo 'server { \
        listen 8080; \
        server_name _; \
        root /var/www/html; \
        index index.php; \
        location ~ \.php$ { \
            fastcgi_pass 127.0.0.1:9000; \
            fastcgi_index index.php; \
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
            include fastcgi_params; \
        } \
        location / { \
            try_files $uri $uri/ /index.php?$query_string; \
        } \
    }' > /etc/nginx/sites-enabled/default

# 6️⃣ Crear script de inicio
RUN echo '#!/bin/bash\nphp-fpm\nnginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

EXPOSE 8080

# 7️⃣ Ejecutar script
CMD ["/start.sh"]
