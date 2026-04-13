# Usamos PHP 8.2 con Apache (tu versión local)
FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
# IMPORTANTE: Se añade libzip-dev, vital para compilar la extensión ext-zip de PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Instalar extensiones de PHP
# IMPORTANTE: Se añade 'zip' al final para que maatwebsite/excel funcione
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite (Vital para rutas Laravel)
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP (Optimizadas para producción)
# Ahora esto no fallará porque ext-zip ya está en el sistema
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos (Crucial para logs y caché)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar Apache para servir desde /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiamos el script de entrada al contenedor (Para migrar)
COPY docker-entrypoint.sh /usr/local/bin/

# Nos aseguramos de que tenga permisos de ejecución dentro de Docker
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer el puerto 80
EXPOSE 80

# Usamos el script como punto de entrada
ENTRYPOINT ["docker-entrypoint.sh"]
