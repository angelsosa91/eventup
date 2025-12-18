#!/bin/bash

# Script de deploy completo para producción con backup y rollback
# Incluye: backup automático, modo mantenimiento, verificación de salud

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración
BACKUP_DIR="backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/backup_${TIMESTAMP}.sql"
DB_CONTAINER="neo-erp-db-1"  # Ajusta según tu configuración
APP_URL="https://demo-erp.neosystem.com.py"  # Ajusta tu URL

# Función para mostrar mensajes
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Función para hacer backup de la base de datos
backup_database() {
    log_info "Creando backup de la base de datos..."

    # Crear directorio de backups si no existe
    mkdir -p $BACKUP_DIR

    # Hacer backup
    docker compose exec -T db mysqldump \
        -u root \
        -p${MYSQL_ROOT_PASSWORD:-secret} \
        neo_erp > $BACKUP_FILE 2>/dev/null || {
        log_error "Error al crear backup de la base de datos"
        return 1
    }

    # Verificar que el backup se creó correctamente
    if [ -s "$BACKUP_FILE" ]; then
        BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        log_success "Backup creado: $BACKUP_FILE ($BACKUP_SIZE)"

        # Mantener solo los últimos 5 backups
        ls -t $BACKUP_DIR/backup_*.sql | tail -n +6 | xargs -r rm
        log_info "Backups antiguos limpiados (manteniendo últimos 5)"
    else
        log_error "El archivo de backup está vacío"
        return 1
    fi
}

# Función para restaurar backup
restore_backup() {
    log_warning "Restaurando backup desde: $BACKUP_FILE"
    docker compose exec -T db mysql \
        -u root \
        -p${MYSQL_ROOT_PASSWORD:-secret} \
        neo_erp < $BACKUP_FILE
    log_success "Base de datos restaurada"
}

# Función para activar modo mantenimiento
enable_maintenance() {
    log_info "Activando modo mantenimiento..."
    docker compose exec app php artisan down --retry=60 --secret="deploy-${TIMESTAMP}"
    log_success "Modo mantenimiento activado"
    log_info "Para acceder durante mantenimiento: ${APP_URL}/deploy-${TIMESTAMP}"
}

# Función para desactivar modo mantenimiento
disable_maintenance() {
    log_info "Desactivando modo mantenimiento..."
    docker compose exec app php artisan up
    log_success "Modo mantenimiento desactivado"
}

# Función para verificar salud de la aplicación
check_health() {
    log_info "Verificando salud de la aplicación..."

    # Verificar que los contenedores estén corriendo
    if ! docker compose ps | grep -q "Up"; then
        log_error "Los contenedores no están corriendo"
        return 1
    fi

    # Verificar que la aplicación responda
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" ${APP_URL}/login 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" -eq 200 ] || [ "$HTTP_CODE" -eq 302 ]; then
        log_success "Aplicación respondiendo correctamente (HTTP $HTTP_CODE)"
    else
        log_error "Aplicación no responde correctamente (HTTP $HTTP_CODE)"
        return 1
    fi
}

# Función para rollback
rollback() {
    log_error "Deploy falló, iniciando rollback..."

    # Restaurar código
    git reset --hard HEAD@{1}

    # Restaurar base de datos
    restore_backup

    # Limpiar cachés
    docker compose exec app php artisan optimize:clear

    # Desactivar modo mantenimiento
    disable_maintenance

    log_success "Rollback completado"
    exit 1
}

# Trap para capturar errores y hacer rollback
trap 'rollback' ERR

# ============================================
# INICIO DEL DEPLOY
# ============================================

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}🚀 Iniciando Deploy a Producción${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Paso 1: Backup de la base de datos
backup_database

# Paso 2: Activar modo mantenimiento
enable_maintenance

# Paso 3: Pull del código
log_info "Descargando última versión del código..."
git pull origin main
log_success "Código actualizado"

# Paso 4: Verificar cambios en composer
if git diff HEAD@{1} --name-only | grep -q "composer.json\|composer.lock"; then
    log_info "Detectados cambios en dependencias..."
    docker compose exec app composer install --no-dev --optimize-autoloader
    log_success "Dependencias actualizadas"
fi

# Paso 5: Limpiar cachés
log_info "Limpiando cachés..."
docker compose exec app php artisan optimize:clear
log_success "Cachés limpiados"

# Paso 6: Ejecutar migraciones
if git diff HEAD@{1} --name-only | grep -q "database/migrations"; then
    log_warning "Detectados cambios en migraciones, ejecutando migrate..."
    docker compose exec app php artisan migrate --force
    log_success "Migraciones ejecutadas"
else
    log_info "No hay cambios en migraciones"
fi

# Paso 7: Ejecutar seeders si hay cambios en permisos
if git diff HEAD@{1} --name-only | grep -q "database/seeders/PermissionSeeder.php"; then
    log_info "Detectados cambios en permisos, ejecutando seeder..."
    docker compose exec app php artisan db:seed --class=PermissionSeeder --force
    log_success "Permisos actualizados"
fi

# Paso 8: Optimizar para producción
log_info "Optimizando aplicación..."
docker compose exec app php artisan optimize
log_success "Aplicación optimizada"

# Paso 9: Reiniciar servicios
log_info "Reiniciando servicios..."
docker compose restart app worker scheduler
sleep 5  # Esperar a que los servicios inicien
log_success "Servicios reiniciados"

# Paso 10: Verificar salud
check_health

# Paso 11: Desactivar modo mantenimiento
disable_maintenance

# Paso 12: Verificar estado final
log_info "Verificando estado de contenedores..."
docker compose ps

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Deploy Completado Exitosamente!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "📦 Backup: ${BACKUP_FILE}"
echo -e "🌐 URL: ${APP_URL}"
echo -e "⏱️  Tiempo total: ~2 minutos"
echo ""
echo -e "${YELLOW}Próximos pasos:${NC}"
echo -e "1. Verificar la aplicación en: ${APP_URL}"
echo -e "2. Revisar logs: docker compose logs -f app"
echo -e "3. Monitorear errores durante 10 minutos"
echo ""
