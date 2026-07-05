#!/bin/bash
set -e

# Refrescar caches de Laravel en runtime para evitar depender de comandos manuales en el servidor.
echo "Limpiando caches de Laravel..."
if ! php artisan optimize:clear; then
	echo "Advertencia: No se pudo limpiar la cache de Laravel. Continuando arranque."
fi

echo "Generando cache de configuracion..."
if ! php artisan config:cache; then
	echo "Advertencia: No se pudo generar config:cache. Continuando arranque."
fi

# Ejecutar migraciones con reintentos usando la configuración SSL de Laravel
echo "Ejecutando migraciones (${DB_HOST}:${DB_PORT})..."

max_retries=10
retry=0

until php artisan migrate --force; do
	retry=$((retry + 1))
	if [ "$retry" -ge "$max_retries" ]; then
		echo "Advertencia: Las migraciones no pudieron ejecutarse tras ${max_retries} intentos. El servidor continuará iniciándose."
		break
	fi
	echo "Reintentando migraciones en 3 segundos (intento ${retry}/${max_retries})..."
	sleep 3
done

# Iniciar Apache en primer plano
echo "Iniciando servidor en puerto 80..."
exec apache2-foreground
