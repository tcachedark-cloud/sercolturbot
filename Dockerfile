FROM php:8.2-cli

# Instalar extensiones
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copiar proyecto
COPY . /app
WORKDIR /app

# Railway usa variable PORT
ENV PORT=8080

# Usar el puerto de Railway
CMD php -S 0.0.0.0:${PORT} -t /app
