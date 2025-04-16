FROM php:8.2-cli

# Copia el contenido de tu proyecto
WORKDIR /app
COPY . .

# Instala extensiones si necesitas (opcional)
# RUN docker-php-ext-install curl

# Comando que mantiene el contenedor activo (y permite recibir requests si usas webhook)
CMD ["php", "-S", "0.0.0.0:80", "index.php"]