# Script de despliegue del sistema de permisos en Docker (PowerShell)
# Uso: .\deploy-permissions-docker.ps1 [nombre_contenedor]

param(
    [string]$ContainerName = "neo-erp_app_1"
)

Write-Host "======================================" -ForegroundColor Blue
Write-Host "  Despliegue Sistema de Permisos" -ForegroundColor Blue
Write-Host "======================================" -ForegroundColor Blue
Write-Host ""

# Verificar que el contenedor existe
Write-Host "Verificando contenedor: $ContainerName" -ForegroundColor Yellow
$containerExists = docker ps --format "{{.Names}}" | Select-String -Pattern $ContainerName -Quiet

if (-not $containerExists) {
    Write-Host "Error: El contenedor $ContainerName no está corriendo" -ForegroundColor Red
    Write-Host "Contenedores disponibles:" -ForegroundColor Yellow
    docker ps --format "table {{.Names}}\t{{.Status}}"
    exit 1
}
Write-Host "✓ Contenedor encontrado" -ForegroundColor Green
Write-Host ""

# 1. Actualizar autoload de Composer
Write-Host "[1/5] Actualizando autoload de Composer..." -ForegroundColor Yellow
docker exec -it $ContainerName composer dump-autoload --optimize
Write-Host "✓ Autoload actualizado" -ForegroundColor Green
Write-Host ""

# 2. Ejecutar seeder de permisos
Write-Host "[2/5] Ejecutando seeder de permisos..." -ForegroundColor Yellow
docker exec -it $ContainerName php artisan db:seed --class=PermissionSeeder
Write-Host "✓ Permisos actualizados" -ForegroundColor Green
Write-Host ""

# 3. Ejecutar seeder de roles
Write-Host "[3/5] Ejecutando seeder de roles..." -ForegroundColor Yellow
docker exec -it $ContainerName php artisan db:seed --class=RolesSeeder
Write-Host "✓ Roles creados" -ForegroundColor Green
Write-Host ""

# 4. Limpiar cachés
Write-Host "[4/5] Limpiando cachés..." -ForegroundColor Yellow
docker exec -it $ContainerName php artisan config:clear
docker exec -it $ContainerName php artisan cache:clear
docker exec -it $ContainerName php artisan view:clear
docker exec -it $ContainerName php artisan route:clear
Write-Host "✓ Cachés limpiados" -ForegroundColor Green
Write-Host ""

# 5. Verificar instalación
Write-Host "[5/5] Verificando instalación..." -ForegroundColor Yellow
Write-Host ""
docker exec -it $ContainerName php artisan tinker --execute="use App\Models\Role; use App\Models\Permission; echo '=== VERIFICACIÓN ===' . PHP_EOL; echo 'Permisos totales: ' . Permission::count() . PHP_EOL; echo 'Roles totales: ' . Role::count() . PHP_EOL;"
Write-Host ""

Write-Host "======================================" -ForegroundColor Green
Write-Host "  ✓ Despliegue completado exitosamente" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host ""
Write-Host "Próximos pasos:" -ForegroundColor Yellow
Write-Host "1. Accede al sistema con tu usuario super-admin"
Write-Host "2. Ve a Gestión de Usuarios"
Write-Host "3. Asigna los roles apropiados a cada usuario"
Write-Host ""
