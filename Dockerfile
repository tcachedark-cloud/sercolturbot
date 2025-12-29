FROM php:8.2-fpm

# 1️⃣ Instalar Nginx y supervisor
RUN apt-get update && apt-get install -y nginx supervisor && rm -rf /var/lib/apt/lists/*

# 2️⃣ Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 3️⃣ Copiar proyecto
COPY . /var/www/html

WORKDIR /var/www/html

# 4️⃣ Permisos
RUN chown -R www-data:www-data /var/www/html

# 5️⃣ Crear configuración personalizada de Nginx con reescrituras
RUN mkdir -p /etc/nginx/sites-enabled && \
    echo 'server { \
        listen 8080; \
        server_name _; \
        root /var/www/html; \
        index index.php index.html; \
        client_max_body_size 100M; \
        location / { \
            try_files $uri $uri/ /index.php?$query_string; \
        } \
        location ~ \.php$ { \
            fastcgi_pass localhost:9000; \
            fastcgi_index index.php; \
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
            include fastcgi_params; \
        } \
        location ~ /\. { \
            deny all; \
        } \
    }' > /etc/nginx/sites-enabled/default

# 6️⃣ Crear configuración de supervisor
RUN mkdir -p /etc/supervisor/conf.d && \
    echo '[supervisord]\nuser=root\nnodaemon=true\n\n[program:php-fpm]\ncommand=php-fpm\nautorestart=true\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautorestart=true' > /etc/supervisor/conf.d/services.conf

EXPOSE 8080

# 7️⃣ Ejecutar supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
