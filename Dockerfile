FROM php:8.3-apache

WORKDIR /var/www/html

# Copiar el código
COPY . .

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Build de assets
RUN npm install && npm run build

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage
RUN chown -R www-data:www-data /var/www/html/bootstrap/cache

# Configurar Apache
RUN a2enmod rewrite

EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=8080