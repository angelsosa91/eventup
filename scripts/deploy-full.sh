#!/bin/bash

# Script de deploy COMPLETO (con rebuild)
# Usar solo cuando:
# - Cambien las dependencias del sistema (Dockerfile)
# - Primera instalación
# - Cambios en configuración de PHP/Nginx/Supervisor

set -e

echo "🏗️  Iniciando deploy COMPLETO (con rebuild)..."

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Paso 1: Pull del código
echo -e "${YELLOW}📥 Descargando última versión del código...${NC}"
git pull origin main

# Paso 2: Detener contenedores
echo -e "${YELLOW}🛑 Deteniendo contenedores...${NC}"
docker compose down

# Paso 3: Reconstruir imágenes
echo -e "${YELLOW}🏗️  Reconstruyendo imágenes Docker...${NC}"
echo -e "${RED}⚠️  ADVERTENCIA: Esto puede tomar 5-10 minutos${NC}"
docker compose build --no-cache

# Paso 4: Levantar servicios
echo -e "${YELLOW}🚀 Levantando servicios...${NC}"
docker compose up -d

# Paso 5: Esperar a que la aplicación esté lista
echo -e "${YELLOW}⏳ Esperando a que la aplicación esté lista...${NC}"
sleep 10

# Paso 6: Ejecutar migraciones
echo -e "${YELLOW}🗄️  Ejecutando migraciones...${NC}"
docker compose exec app php artisan migrate --force

# Paso 7: Optimizar para producción
echo -e "${YELLOW}⚡ Optimizando aplicación...${NC}"
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Paso 8: Verificar estado
echo -e "${YELLOW}✅ Verificando estado de contenedores...${NC}"
docker compose ps

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Deploy COMPLETO exitoso!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "⚠️  Nota: Este tipo de deploy toma más tiempo."
echo -e "💡 Para futuros cambios de código, usa: ./scripts/deploy-fast.sh"
echo ""
