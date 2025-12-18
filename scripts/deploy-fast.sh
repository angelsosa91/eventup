#!/bin/bash

# Script de deploy rápido para producción
# Este script NO reconstruye la imagen Docker, solo actualiza el código

set -e

echo "🚀 Iniciando deploy rápido..."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Paso 1: Pull del código
echo -e "${YELLOW}📥 Descargando última versión del código...${NC}"
git pull origin main

# Paso 2: Verificar si hay cambios en composer.json
if git diff HEAD@{1} --name-only | grep -q "composer.json\|composer.lock"; then
    echo -e "${YELLOW}📦 Detectados cambios en dependencias, ejecutando composer install...${NC}"
    docker compose exec app composer install --no-dev --optimize-autoloader
fi

# Paso 3: Limpiar cachés de Laravel
echo -e "${YELLOW}🧹 Limpiando cachés...${NC}"
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear

# Paso 4: Optimizar para producción
echo -e "${YELLOW}⚡ Optimizando aplicación...${NC}"
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Paso 5: Ejecutar migraciones si hay
if git diff HEAD@{1} --name-only | grep -q "database/migrations"; then
    echo -e "${YELLOW}🗄️  Detectados cambios en migraciones, ejecutando migrate...${NC}"
    docker compose exec app php artisan migrate --force
fi

# Paso 6: Reiniciar servicios (sin rebuild)
echo -e "${YELLOW}🔄 Reiniciando contenedores...${NC}"
docker compose restart app worker scheduler

# Paso 7: Verificar estado
echo -e "${YELLOW}✅ Verificando estado de contenedores...${NC}"
docker compose ps

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Deploy completado exitosamente!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Tiempo total: ~30 segundos"
echo -e "URL: https://demo-erp.neosystem.com.py"
echo ""
