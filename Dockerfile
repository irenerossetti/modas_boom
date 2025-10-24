FROM php:8.3-apache

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip nodejs npm

# Instalar dependencias específicas de PostgreSQL
RUN apt-get install -y libpq-dev

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# LÍNEA CORREGIDA - con guiones bajos
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]