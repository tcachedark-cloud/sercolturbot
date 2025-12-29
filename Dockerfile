FROM php:8.2-cli

# Instalar extensiones
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copiar proyecto
COPY . /app
WORKDIR /app

# Exponer puerto
EXPOSE 8080

# Usar servidor PHP integrado (más simple y confiable)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]
