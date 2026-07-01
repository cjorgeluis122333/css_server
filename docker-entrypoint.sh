#!/bin/bash
set -e

# Ejecutar migraciones con reintentos usando la configuración SSL de Laravel
echo "Ejecutando migraciones (${DB_HOST}:${DB_PORT})..."

max_retries=10
retry=0
migrated=false

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
