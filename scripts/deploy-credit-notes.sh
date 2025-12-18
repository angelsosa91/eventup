#!/bin/bash

# Script específico para desplegar el módulo de Notas de Crédito
# Este script es seguro y hace backup antes de aplicar cambios

set -e

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuración
BACKUP_DIR="backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/backup_credit_notes_${TIMESTAMP}.sql"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}📝 Deploy: Módulo Notas de Crédito${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Función para logging
log_info() { echo -e "${BLUE}ℹ️  $1${NC}"; }
log_success() { echo -e "${GREEN}✅ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }
log_error() { echo -e "${RED}❌ $1${NC}"; }

# Crear directorio de backups
mkdir -p $BACKUP_DIR

# Paso 1: Backup de la base de datos
log_info "Creando backup de seguridad..."
docker compose exec -T db mysqldump \
    -u root \
    -p${MYSQL_ROOT_PASSWORD:-secret} \
    neo_erp > $BACKUP_FILE 2>/dev/null || {
    log_error "Error al crear backup"
    exit 1
}

if [ -s "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_success "Backup creado: $BACKUP_FILE ($BACKUP_SIZE)"
else
    log_error "Error: backup vacío"
    exit 1
fi

# Paso 2: Activar modo mantenimiento
log_info "Activando modo mantenimiento..."
docker compose exec app php artisan down --retry=60
log_success "Modo mantenimiento activado"

# Paso 3: Ejecutar migración de notas de crédito
log_warning "Ejecutando migración: credit_notes..."
docker compose exec app php artisan migrate --path=database/migrations/2025_12_10_000001_create_credit_notes_table.php --force

if [ $? -eq 0 ]; then
    log_success "Migración ejecutada correctamente"
else
    log_error "Error en migración, restaurando backup..."
    docker compose exec -T db mysql \
        -u root \
        -p${MYSQL_ROOT_PASSWORD:-secret} \
        neo_erp < $BACKUP_FILE
    docker compose exec app php artisan up
    exit 1
fi

# Paso 4: Ejecutar seeder de permisos
log_info "Actualizando permisos..."
docker compose exec app php artisan db:seed --class=PermissionSeeder --force
log_success "Permisos actualizados"

# Paso 5: Verificar que las tablas se crearon
log_info "Verificando tablas..."
TABLES=$(docker compose exec -T db mysql \
    -u root \
    -p${MYSQL_ROOT_PASSWORD:-secret} \
    -e "USE neo_erp; SHOW TABLES LIKE 'credit%';" 2>/dev/null | grep credit)

if echo "$TABLES" | grep -q "credit_notes"; then
    log_success "Tabla 'credit_notes' creada ✓"
else
    log_error "Tabla 'credit_notes' NO encontrada"
fi

if echo "$TABLES" | grep -q "credit_note_items"; then
    log_success "Tabla 'credit_note_items' creada ✓"
else
    log_error "Tabla 'credit_note_items' NO encontrada"
fi

# Paso 6: Verificar permisos
log_info "Verificando permisos..."
PERMS=$(docker compose exec -T db mysql \
    -u root \
    -p${MYSQL_ROOT_PASSWORD:-secret} \
    -e "USE neo_erp; SELECT COUNT(*) as count FROM permissions WHERE slug LIKE 'credit-notes%';" 2>/dev/null | tail -n 1)

if [ "$PERMS" -ge 4 ]; then
    log_success "Permisos de notas de crédito creados ($PERMS) ✓"
else
    log_warning "Permisos incompletos (encontrados: $PERMS, esperados: 4)"
fi

# Paso 7: Limpiar y optimizar cachés
log_info "Limpiando cachés..."
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
log_success "Cachés optimizados"

# Paso 8: Desactivar modo mantenimiento
log_info "Desactivando modo mantenimiento..."
docker compose exec app php artisan up
log_success "Modo mantenimiento desactivado"

# Resumen final
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Deploy Completado Exitosamente!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}📋 Resumen:${NC}"
echo -e "   • Tablas creadas: credit_notes, credit_note_items"
echo -e "   • Permisos agregados: 4 (view, create, confirm, cancel)"
echo -e "   • Backup guardado en: ${BACKUP_FILE}"
echo ""
echo -e "${YELLOW}🧪 Testing:${NC}"
echo -e "   1. Acceder a: https://demo-erp.neosystem.com.py/credit-notes"
echo -e "   2. Verificar menú: Ventas > Notas de Crédito"
echo -e "   3. Crear nota de crédito de prueba"
echo -e "   4. Confirmar y verificar PDF"
echo -e "   5. Verificar asiento contable generado"
echo ""
echo -e "${GREEN}✅ Módulo de Notas de Crédito listo para usar!${NC}"
echo ""
