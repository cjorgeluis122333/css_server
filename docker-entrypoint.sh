#!/bin/bash

# Esperar un momento a que la base de datos esté lista (opcional pero recomendado)
echo "Esperando a la base de datos..."

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Iniciar Apache en primer plano
echo "Iniciando servidor..."
exec apache2-foreground
