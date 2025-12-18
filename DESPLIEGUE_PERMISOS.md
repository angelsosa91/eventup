# Plan de Despliegue del Sistema de Permisos a Producción

## 📋 Preparación Pre-Despliegue

### 1. Verificar en Desarrollo
Antes de llevar a producción, asegúrate de que todo funciona correctamente:

```bash
# En desarrollo/local
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolesSeeder
composer dump-autoload
```

Prueba con usuarios de cada rol para verificar que todo funciona.

---

## 🚀 Opción 1: Despliegue Automático (RECOMENDADO)

### Paso 1: Subir los Archivos al Servidor
```bash
# Desde tu repositorio Git
git add .
git commit -m "feat: Sistema completo de permisos con roles predefinidos"
git push origin main

# En el servidor de producción
cd /ruta/de/produccion
git pull origin main
```

### Paso 2: Actualizar Dependencias de Composer
```bash
# En producción
composer dump-autoload --optimize
```

### Paso 3: Ejecutar Seeders en Producción
```bash
# Ejecutar seeder de permisos (actualiza sin eliminar existentes)
php artisan db:seed --class=PermissionSeeder

# Ejecutar seeder de roles (crea roles sin duplicar)
php artisan db:seed --class=RolesSeeder
```

**IMPORTANTE**: Los seeders usan `updateOrCreate`, por lo que:
- ✅ NO eliminarán datos existentes
- ✅ Actualizarán permisos y roles existentes
- ✅ Crearán los nuevos roles predefinidos
- ✅ Mantendrán las asignaciones de roles a usuarios existentes

### Paso 4: Limpiar Cachés
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Paso 5: Optimizar para Producción
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Opción 2: Despliegue Manual (Más Control)

Si prefieres tener control total sobre qué se crea:

### Paso 1: Conectarse a la Base de Datos de Producción

Desde tu herramienta de gestión de BD (phpMyAdmin, MySQL Workbench, DBeaver):

### Paso 2: Verificar Permisos Existentes
```sql
SELECT COUNT(*) as total FROM permissions;
SELECT DISTINCT module FROM permissions ORDER BY module;
```

### Paso 3: Insertar Nuevos Permisos (SQL)

Si faltan permisos, puedes ejecutar el seeder O insertar manualmente:

```sql
-- Ejemplo: Insertar permisos de conciliación bancaria si no existen
INSERT INTO permissions (name, slug, module, created_at, updated_at) VALUES
('Ver Conciliaciones Bancarias', 'bank-reconciliations.view', 'conciliacion_bancaria', NOW(), NOW()),
('Crear Conciliaciones Bancarias', 'bank-reconciliations.create', 'conciliacion_bancaria', NOW(), NOW()),
('Editar Conciliaciones Bancarias', 'bank-reconciliations.edit', 'conciliacion_bancaria', NOW(), NOW()),
('Publicar Conciliaciones', 'bank-reconciliations.post', 'conciliacion_bancaria', NOW(), NOW()),
('Cancelar Conciliaciones', 'bank-reconciliations.cancel', 'conciliacion_bancaria', NOW(), NOW()),
('Eliminar Conciliaciones', 'bank-reconciliations.delete', 'conciliacion_bancaria', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = VALUES(name), updated_at = NOW();
```

### Paso 4: Crear Roles Manualmente

```sql
-- Verificar roles existentes
SELECT id, name, slug, is_system FROM roles;

-- Insertar rol Contador (ejemplo)
INSERT INTO roles (tenant_id, name, slug, description, is_system, created_at, updated_at)
VALUES (1, 'Contador', 'contador', 'Gestión contable, financiera y bancaria completa', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE description = VALUES(description), updated_at = NOW();

-- Obtener el ID del rol recién creado
SET @contador_id = LAST_INSERT_ID();

-- Asignar permisos al rol Contador
INSERT INTO permission_role (permission_id, role_id)
SELECT p.id, @contador_id
FROM permissions p
WHERE p.module IN (
    'contabilidad', 'asientos_contables', 'libro_mayor', 'estados_financieros',
    'bancos', 'cuentas_bancarias', 'transacciones_bancarias', 'cheques',
    'conciliacion_bancaria', 'cuentas_cobrar', 'cuentas_pagar', 'gastos', 'reportes'
)
OR p.slug IN ('customers.view', 'suppliers.view', 'products.view')
ON DUPLICATE KEY UPDATE role_id = role_id;
```

---

## 🛡️ Opción 3: Despliegue con Backup (MÁS SEGURO)

### Paso 1: Crear Backup de Producción
```bash
# Backup de la base de datos
php artisan db:backup
# O manualmente:
mysqldump -u usuario -p nombre_bd > backup_pre_permisos_$(date +%Y%m%d).sql
```

### Paso 2: Ejecutar Seeders con Precaución
```bash
# Primero solo permisos
php artisan db:seed --class=PermissionSeeder

# Verificar que se crearon correctamente
php artisan tinker --execute="echo 'Total permisos: ' . \App\Models\Permission::count();"

# Si todo está bien, ejecutar roles
php artisan db:seed --class=RolesSeeder

# Verificar roles
php artisan tinker --execute="echo 'Total roles: ' . \App\Models\Role::count();"
```

### Paso 3: Verificar en Producción
- Acceder al sistema con usuario super-admin
- Ir a Gestión de Roles
- Verificar que aparecen los 6 nuevos roles
- Verificar que cada rol tiene permisos asignados

---

## 📊 Script de Verificación Post-Despliegue

Ejecuta este script para verificar que todo está correcto:

```bash
php artisan tinker
```

```php
use App\Models\Role;
use App\Models\Permission;

echo "=== VERIFICACIÓN POST-DESPLIEGUE ===" . PHP_EOL;
echo "Total Permisos: " . Permission::count() . PHP_EOL;
echo "Total Roles: " . Role::count() . PHP_EOL . PHP_EOL;

echo "Roles con sus permisos:" . PHP_EOL;
Role::all()->each(function($role) {
    echo "- {$role->name}: {$role->permissions()->count()} permisos" . PHP_EOL;
});
```

---

## ⚠️ Consideraciones Importantes

### 1. Usuarios Existentes
Los usuarios existentes **NO** se verán afectados. Sus roles y permisos actuales se mantienen.

### 2. Asignar Roles a Usuarios Existentes
Después del despliegue, deberás asignar los nuevos roles a los usuarios según corresponda:

**Opción A: Desde la Interfaz**
- Ir a Gestión de Usuarios
- Editar cada usuario
- Asignar el rol apropiado

**Opción B: Desde SQL**
```sql
-- Ver usuarios sin roles o con roles antiguos
SELECT u.id, u.name, u.email, GROUP_CONCAT(r.name) as roles
FROM users u
LEFT JOIN role_user ru ON u.id = ru.user_id
LEFT JOIN roles r ON ru.role_id = r.id
GROUP BY u.id;

-- Asignar rol a un usuario específico
INSERT INTO role_user (user_id, role_id)
VALUES (
    (SELECT id FROM users WHERE email = 'usuario@ejemplo.com'),
    (SELECT id FROM roles WHERE slug = 'contador')
);
```

### 3. Permisos Antiguos
Si tienes roles antiguos con asignaciones manuales de permisos, estos se mantendrán. Los nuevos roles son adicionales.

### 4. Migración de Usuarios a Nuevos Roles

Si quieres migrar usuarios de roles antiguos a nuevos:

```sql
-- Ejemplo: Migrar usuarios del rol 'admin' antiguo al nuevo 'administrador'
UPDATE role_user
SET role_id = (SELECT id FROM roles WHERE slug = 'administrador')
WHERE role_id = (SELECT id FROM roles WHERE slug = 'admin-antiguo');
```

---

## 📝 Checklist de Despliegue

- [ ] Backup de base de datos de producción
- [ ] Subir código al servidor (git pull)
- [ ] Ejecutar `composer dump-autoload`
- [ ] Ejecutar `php artisan db:seed --class=PermissionSeeder`
- [ ] Ejecutar `php artisan db:seed --class=RolesSeeder`
- [ ] Limpiar cachés de Laravel
- [ ] Verificar que los roles se crearon correctamente
- [ ] Asignar roles a usuarios de producción
- [ ] Probar acceso con diferentes roles
- [ ] Verificar que los botones se ocultan según permisos
- [ ] Monitorear logs por errores 403

---

## 🔄 Rollback en Caso de Problemas

Si algo sale mal, puedes revertir:

```bash
# Restaurar backup de BD
mysql -u usuario -p nombre_bd < backup_pre_permisos_YYYYMMDD.sql

# O revertir código
git revert HEAD
git push origin main
```

---

## 💡 Recomendación Final

**La mejor opción es la Opción 1 (Despliegue Automático)** porque:
- ✅ Es rápida y segura
- ✅ Los seeders usan `updateOrCreate` (no destruyen datos)
- ✅ Puedes revertir fácilmente con Git
- ✅ Es repetible en cualquier ambiente

Solo asegúrate de:
1. Hacer backup antes
2. Ejecutar en horario de bajo tráfico
3. Tener acceso de super-admin para asignar roles después
