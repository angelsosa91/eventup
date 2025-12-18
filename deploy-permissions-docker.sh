#!/bin/bash

# Script de despliegue del sistema de permisos en Docker
# Uso: ./deploy-permissions-docker.sh [nombre_contenedor]

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Nombre del contenedor (puedes pasarlo como argumento)
CONTAINER_NAME=${1:-"neo-erp_app_1"}

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}  Despliegue Sistema de Permisos${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Verificar que el contenedor existe
echo -e "${YELLOW}Verificando contenedor: ${CONTAINER_NAME}${NC}"
if ! docker ps | grep -q "${CONTAINER_NAME}"; then
    echo -e "${RED}Error: El contenedor ${CONTAINER_NAME} no está corriendo${NC}"
    echo -e "${YELLOW}Contenedores disponibles:${NC}"
    docker ps --format "table {{.Names}}\t{{.Status}}"
    exit 1
fi
echo -e "${GREEN}✓ Contenedor encontrado${NC}"
echo ""

# 1. Actualizar autoload de Composer
echo -e "${YELLOW}[1/5] Actualizando autoload de Composer...${NC}"
docker exec -it ${CONTAINER_NAME} composer dump-autoload --optimize
echo -e "${GREEN}✓ Autoload actualizado${NC}"
echo ""

# 2. Ejecutar seeder de permisos
echo -e "${YELLOW}[2/5] Ejecutando seeder de permisos...${NC}"
docker exec -it ${CONTAINER_NAME} php artisan db:seed --class=PermissionSeeder
echo -e "${GREEN}✓ Permisos actualizados${NC}"
echo ""

# 3. Ejecutar seeder de roles
echo -e "${YELLOW}[3/5] Ejecutando seeder de roles...${NC}"
docker exec -it ${CONTAINER_NAME} php artisan db:seed --class=RolesSeeder
echo -e "${GREEN}✓ Roles creados${NC}"
echo ""

# 4. Limpiar cachés
echo -e "${YELLOW}[4/5] Limpiando cachés...${NC}"
docker exec -it ${CONTAINER_NAME} php artisan config:clear
docker exec -it ${CONTAINER_NAME} php artisan cache:clear
docker exec -it ${CONTAINER_NAME} php artisan view:clear
docker exec -it ${CONTAINER_NAME} php artisan route:clear
echo -e "${GREEN}✓ Cachés limpiados${NC}"
echo ""

# 5. Verificar instalación
echo -e "${YELLOW}[5/5] Verificando instalación...${NC}"
echo ""
docker exec -it ${CONTAINER_NAME} php artisan tinker --execute="
use App\Models\Role;
use App\Models\Permission;
echo '=== VERIFICACIÓN ===' . PHP_EOL;
echo 'Permisos totales: ' . Permission::count() . PHP_EOL;
echo 'Roles totales: ' . Role::count() . PHP_EOL;
echo PHP_EOL . 'Roles creados:' . PHP_EOL;
foreach(Role::orderBy('id')->get() as \$r) {
    echo '  - ' . \$r->name . ' (' . \$r->slug . ') - ' . \$r->permissions()->count() . ' permisos' . PHP_EOL;
}
"
echo ""

echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}  ✓ Despliegue completado exitosamente${NC}"
echo -e "${GREEN}======================================${NC}"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo "1. Accede al sistema con tu usuario super-admin"
echo "2. Ve a Gestión de Usuarios"
echo "3. Asigna los roles apropiados a cada usuario"
echo ""
