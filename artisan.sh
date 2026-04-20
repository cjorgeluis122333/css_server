#!/bin/bash

# Script para ejecutar comandos comunes de Laravel en Docker
# Uso: ./artisan.sh migrate, ./artisan.sh tinker, etc.

COMMAND=$1
ARGS="${@:2}"

case "$COMMAND" in
  # Alias cortos
  m|migrate)
    echo "🔄 Ejecutando migraciones..."
    sudo docker compose exec app php artisan migrate $ARGS
    ;;
  
  rollback)
    echo "⏮️  Deshacer última migración..."
    sudo docker compose exec app php artisan migrate:rollback $ARGS
    ;;
  
  refresh)
    echo "🔄 Refresh (rollback + migrate)..."
    sudo docker compose exec app php artisan migrate:refresh $ARGS
    ;;
  
  reset)
    echo "⚠️  Reset completo de migraciones..."
    sudo docker compose exec app php artisan migrate:reset $ARGS
    ;;
  
  seed)
    echo "🌱 Ejecutando seeders..."
    sudo docker compose exec app php artisan db:seed $ARGS
    ;;
  
  tinker|tin)
    echo "🔮 Abriendo Tinker..."
    sudo docker compose exec app php artisan tinker
    ;;
  
  routes)
    echo "🗺️  Listando rutas..."
    sudo docker compose exec app php artisan route:list $ARGS
    ;;
  
  config:clear)
    echo "🧹 Limpiando cache de config..."
    sudo docker compose exec app php artisan config:clear $ARGS
    ;;
  
  logs)
    echo "📋 Logs en tiempo real (app)..."
    sudo docker compose logs app -f
    ;;
  
  logs:mysql)
    echo "📋 Logs en tiempo real (mysql)..."
    sudo docker compose logs mysql -f
    ;;
  
  ps)
    echo "📦 Estado de contenedores..."
    sudo docker compose ps
    ;;
  
  up)
    echo "🚀 Levantando contenedores..."
    sudo docker compose up -d --build && sudo docker compose ps
    ;;
  
  down)
    echo "⛔ Parando contenedores..."
    sudo docker compose down
    ;;
  
  down-all)
    echo "⛔ Parando y borrando TODO (incluida BD)..."
    sudo docker compose down -v
    ;;
  
  mysql|db)
    echo "💾 Conectando a MySQL..."
    sudo docker compose exec mysql mysql -uroot -p1 ccv_server
    ;;
  
  bash|sh)
    echo "🐢 Acceso bash al contenedor app..."
    sudo docker compose exec app bash
    ;;
  
  *)
    echo "📖 Comandos disponibles:"
    echo ""
    echo "  ./artisan.sh m|migrate [args]       - Ejecutar migraciones"
    echo "  ./artisan.sh rollback               - Deshacer última migración"
    echo "  ./artisan.sh refresh                - Reset + migrate"
    echo "  ./artisan.sh reset                  - Reset completo"
    echo "  ./artisan.sh seed                   - Ejecutar seeders"
    echo "  ./artisan.sh tinker|tin             - Abrir Tinker"
    echo "  ./artisan.sh routes                 - Listar rutas"
    echo "  ./artisan.sh config:clear           - Limpiar cache"
    echo "  ./artisan.sh logs                   - Logs app en vivo"
    echo "  ./artisan.sh logs:mysql             - Logs mysql en vivo"
    echo "  ./artisan.sh ps                     - Estado contenedores"
    echo "  ./artisan.sh up                     - Levantar app"
    echo "  ./artisan.sh down                   - Parar app"
    echo "  ./artisan.sh down-all               - Parar app + borrar BD"
    echo "  ./artisan.sh mysql|db               - Conectar a MySQL"
    echo "  ./artisan.sh bash|sh                - Acceso bash al contenedor"
    echo ""
    echo "  Para otros comandos artisan, usa:"
    echo "    sudo docker compose exec app php artisan COMANDO"
    ;;
esac
