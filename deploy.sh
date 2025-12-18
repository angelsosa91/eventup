#!/bin/bash

set -e

echo "======================================"
echo "Neo ERP - Script de Deployment"
echo "======================================"
echo ""

docker compose down
docker system prune -a -f

# Check if .env exists
#if [ ! -f .env ]; then
#    echo "Archivo .env no encontrado. Copiando .env.docker..."
#    cp .env.docker .env
#    echo ""
#    echo "IMPORTANTE: Edita el archivo .env y configura:"
#    echo "  - APP_KEY (se puede generar con: php artisan key:generate)"
#    echo "  - DB_PASSWORD"
#    echo "  - DB_ROOT_PASSWORD"
#    echo "  - Otras configuraciones según tu entorno"
#    echo ""
#    read -p "Presiona Enter cuando hayas configurado .env..."
#fi

# Load environment variables
#source .env

echo "1. Construyendo imágenes Docker..."
docker compose build --no-cache

echo ""
echo "2. Iniciando contenedores..."
docker compose up -d

echo ""
echo "3. Verificando conexión a base de datos externa..."
# No necesitamos esperar ya que la BD es externa y ya debe estar disponible

echo ""
echo "4. Generando APP_KEY si no existe..."
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "" ]; then
    docker compose exec app php artisan key:generate --force
fi

echo ""
echo "5. Ejecutando migraciones..."
docker compose exec app php artisan migrate --force

echo ""
echo "6. Ejecutando seeders (primera vez)..."
read -p "¿Deseas ejecutar los seeders? (s/N): " run_seeders
if [ "$run_seeders" == "s" ] || [ "$run_seeders" == "S" ]; then
    docker compose exec app php artisan db:seed --force
fi

echo ""
echo "7. Optimizando aplicación..."
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

echo ""
echo "======================================"
echo "¡Deployment completado exitosamente!"
echo "======================================"
echo ""
echo "La aplicación está disponible en: http://localhost:${APP_PORT:-8080}"
echo ""
echo "Credenciales por defecto:"
echo "  Email: admin@neo-erp.com"
echo "  Password: password"
echo ""
echo "Comandos útiles:"
echo "  - Ver logs: docker-compose logs -f"
echo "  - Detener: docker-compose down"
echo "  - Reiniciar: docker-compose restart"
echo ""
