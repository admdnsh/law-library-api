FROM php:8.2-cli

# Install PDO and MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy all PHP files
COPY . /app/

WORKDIR /app

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080}
