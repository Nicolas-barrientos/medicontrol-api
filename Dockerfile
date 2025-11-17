# Imagen base: PHP con Apache
FROM php:8.2-apache

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Instalar dependencias necesarias + soporte PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_pgsql pgsql zip bcmath

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar DocumentRoot en /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar todo el proyecto
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Permisos Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 80

# Generar clave automáticamente si no existe
CMD if [ ! -f .env ]; then cp .env.example .env; fi && \
    php artisan key:generate --force && \
    apache2-foreground
