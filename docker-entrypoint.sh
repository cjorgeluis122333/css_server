#!/bin/bash
set -e

# Espera activa a MySQL para evitar que las migraciones fallen al iniciar.
echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT}..."

max_retries=30
retry=0
db_connected=false

until php -r "
try {
		new PDO(
				'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
				getenv('DB_USERNAME'),
				getenv('DB_PASSWORD')
		);
		exit(0);
} catch (Throwable $e) {
		exit(1);
}
"; do
	retry=$((retry + 1))
	if [ "$retry" -ge "$max_retries" ]; then
		echo "Advertencia: No se pudo conectar a la base de datos tras ${max_retries} intentos. Continuando de todas formas..."
		db_connected=false
		break
	fi
	sleep 1
done

if [ "$retry" -lt "$max_retries" ]; then
	echo "Base de datos disponible."
	db_connected=true
fi

# Ejecutar migraciones solo si la base de datos está disponible
if [ "$db_connected" = true ]; then
	echo "Ejecutando migraciones..."
	php artisan migrate --force 2>/dev/null || echo "Advertencia: Las migraciones no pudieron ejecutarse. El servidor continuará iniciándose."
else
	echo "Omitiendo migraciones: base de datos no disponible."
fi

# Iniciar Apache en primer plano
echo "Iniciando servidor en puerto 80..."
exec apache2-foreground
