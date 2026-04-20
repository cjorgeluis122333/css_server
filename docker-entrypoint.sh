#!/bin/bash
set -e

# Espera activa a MySQL para evitar que las migraciones fallen al iniciar.
echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT}..."

max_retries=60
retry=0

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
		echo "No se pudo conectar a la base de datos tras ${max_retries} intentos."
		exit 1
	fi
	sleep 2
done

echo "Base de datos disponible."

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

# Iniciar Apache en primer plano
echo "Iniciando servidor..."
exec apache2-foreground
