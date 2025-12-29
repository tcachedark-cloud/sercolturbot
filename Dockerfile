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

# 5️⃣ Configurar PHP-FPM para usar socket Unix
RUN sed -i 's|listen = 9000|listen = /var/run/php-fpm.sock|g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|;listen.owner = nobody|listen.owner = www-data|g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|;listen.group = nobody|listen.group = www-data|g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's|;listen.mode = 0660|listen.mode = 0660|g' /usr/local/etc/php-fpm.d/www.conf

# 6️⃣ Crear configuración de Nginx (sintaxis correcta)
RUN mkdir -p /etc/nginx/sites-enabled && cat > /etc/nginx/sites-enabled/default << 'EOF'
server {
    listen 8080;
    server_name _;
    root /var/www/html;
    index index.php index.html;
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

# 7️⃣ Crear configuración de supervisor (PHP-FPM PRIMERO)
RUN mkdir -p /etc/supervisor/conf.d && cat > /etc/supervisor/conf.d/services.conf << 'EOF'
[supervisord]
user=root
nodaemon=true

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
priority=999
startsecs=0
stopasgroup=true

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=1000
startsecs=5
stopasgroup=true
EOF

EXPOSE 8080

# 8️⃣ Ejecutar supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
