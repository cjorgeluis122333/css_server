# Usamos PHP 8.2 con Apache (tu versión local)
FROM php:8.2-apache

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Instalar extensiones de PHP
# TiDB usa el protocolo MySQL, así que pdo_mysql es lo único que necesitas de base de datos
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Habilitar mod_rewrite (Vital para rutas Laravel)
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP (Optimizadas para producción)
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos (Crucial para logs y caché)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar Apache para servir desde /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiamos el script de entrada al contenedor(Para migrar)
COPY docker-entrypoint.sh /usr/local/bin/

# Nos aseguramos de que tenga permisos de ejecución dentro de Docker
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
# Exponer el puerto 80
EXPOSE 80

# Usamos el script como punto de entrada
ENTRYPOINT ["docker-entrypoint.sh"]
# Comando de inicio
#CMD ["apache2-foreground"]
