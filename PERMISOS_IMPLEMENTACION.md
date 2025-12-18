# Implementación del Sistema de Permisos

## ✅ Componentes Implementados

### 1. Middleware `CheckPermission`
Ubicación: `app/Http/Middleware/CheckPermission.php`

**Características:**
- Verifica si el usuario tiene el permiso requerido
- Super admin tiene acceso a todo automáticamente
- Retorna JSON para peticiones AJAX
- Redirige con mensaje para peticiones normales

### 2. Permisos Actualizados
Se ejecutó el seeder con **168 permisos** agrupados en **22 módulos**:

- usuarios
- roles
- clientes
- proveedores
- productos
- categorias
- ventas
- notas_credito
- remisiones
- compras
- gastos
- inventario
- cuentas_cobrar
- cuentas_pagar
- caja
- bancos
- cuentas_bancarias
- transacciones_bancarias
- cheques
- conciliacion_bancaria (✨ NUEVO)
- contabilidad
- asientos_contables
- libro_mayor
- estados_financieros
- reportes
- configuracion

### 3. Middleware Registrado
El middleware está registrado en `bootstrap/app.php` con el alias `permission`.

---

## 📖 Cómo Aplicar Permisos a las Rutas

### Opción 1: Por Ruta Individual
```php
Route::get('/users', [UserController::class, 'index'])
    ->middleware('permission:users.view')
    ->name('users.index');
```

### Opción 2: Por Grupo de Rutas
```php
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
});
```

### Opción 3: Múltiples Permisos por Módulo
```php
// Usuarios - Solo lectura
Route::middleware('permission:users.view')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
});

// Usuarios - Crear
Route::post('/users', [UserController::class, 'store'])
    ->middleware('permission:users.create')
    ->name('users.store');

// Usuarios - Editar
Route::put('/users/{user}', [UserController::class, 'update'])
    ->middleware('permission:users.edit')
    ->name('users.update');

// Usuarios - Eliminar
Route::delete('/users/{user}', [UserController::class, 'destroy'])
    ->middleware('permission:users.delete')
    ->name('users.destroy');
```

---

## 🎯 Ejemplo Implementado: Conciliación Bancaria

Las rutas de conciliación bancaria ya están listas para aplicar permisos:

```php
// Ver conciliaciones
Route::middleware('permission:bank-reconciliations.view')->group(function () {
    Route::get('/bank-reconciliations', [BankReconciliationController::class, 'index'])
        ->name('bank-reconciliations.index');
    Route::get('/bank-reconciliations/data', [BankReconciliationController::class, 'data'])
        ->name('bank-reconciliations.data');
    Route::get('/bank-reconciliations/{id}', [BankReconciliationController::class, 'show'])
        ->name('bank-reconciliations.show');
});

// Crear conciliaciones
Route::middleware('permission:bank-reconciliations.create')->group(function () {
    Route::get('/bank-reconciliations/create', [BankReconciliationController::class, 'create'])
        ->name('bank-reconciliations.create');
    Route::post('/bank-reconciliations', [BankReconciliationController::class, 'store'])
        ->name('bank-reconciliations.store');
});

// Editar conciliaciones
Route::middleware('permission:bank-reconciliations.edit')->group(function () {
    Route::get('/bank-reconciliations/{id}/edit', [BankReconciliationController::class, 'edit'])
        ->name('bank-reconciliations.edit');
    Route::put('/bank-reconciliations/{id}', [BankReconciliationController::class, 'update'])
        ->name('bank-reconciliations.update');
});

// Publicar conciliaciones
Route::post('/bank-reconciliations/{id}/post', [BankReconciliationController::class, 'post'])
    ->middleware('permission:bank-reconciliations.post')
    ->name('bank-reconciliations.post');

// Cancelar conciliaciones
Route::post('/bank-reconciliations/{id}/cancel', [BankReconciliationController::class, 'cancel'])
    ->middleware('permission:bank-reconciliations.cancel')
    ->name('bank-reconciliations.cancel');

// Eliminar conciliaciones
Route::delete('/bank-reconciliations/{id}', [BankReconciliationController::class, 'destroy'])
    ->middleware('permission:bank-reconciliations.delete')
    ->name('bank-reconciliations.delete');
```

---

## ⚙️ Configuración Inicial de Roles

### Super Admin (Ya existe)
El rol `super-admin` tiene acceso completo automáticamente gracias al método `isSuperAdmin()` en el User model.

### Ejemplo: Crear Rol "Cajero"
```php
$rol = Role::create([
    'tenant_id' => 1,
    'name' => 'Cajero',
    'slug' => 'cashier',
    'description' => 'Manejo de caja y ventas',
]);

// Asignar permisos
$permisos = Permission::whereIn('slug', [
    'sales.view',
    'sales.create',
    'cash-register.view',
    'cash-register.open',
    'cash-register.close',
    'cash-register.movements',
    'customers.view',
    'products.view',
])->pluck('id');

$rol->permissions()->sync($permisos);
```

### Ejemplo: Crear Rol "Contador"
```php
$rol = Role::create([
    'tenant_id' => 1,
    'name' => 'Contador',
    'slug' => 'accountant',
    'description' => 'Gestión contable completa',
]);

// Asignar todos los permisos de contabilidad
$permisos = Permission::whereIn('module', [
    'contabilidad',
    'asientos_contables',
    'libro_mayor',
    'estados_financieros',
    'bancos',
    'cuentas_bancarias',
    'conciliacion_bancaria',
    'reportes',
])->pluck('id');

$rol->permissions()->sync($permisos);
```

---

## 🚀 Implementación Completada

### ✅ Completado - Prioridad ALTA
- [x] Aplicar middleware a rutas críticas en `routes/web.php` (Usuarios, Roles, Conciliaciones)
- [x] Crear roles predefinidos con RolesSeeder
- [x] Asignar permisos a cada rol

### ✅ Completado - Prioridad MEDIA
- [x] Crear directivas Blade para ocultar botones según permisos
- [x] Crear funciones helper globales para verificar permisos
- [x] Actualizar vistas clave con verificación de permisos

### Pendiente - Prioridad BAJA
- [ ] Aplicar middleware a TODAS las rutas restantes en `routes/web.php`
- [ ] Agregar mensaje de "Sin permisos" en el layout principal
- [ ] Crear panel de auditoría de permisos
- [ ] Agregar logs de accesos denegados

---

## 🎨 Directivas Blade y Funciones Helper

### Directivas Blade Disponibles

#### 1. @can - Verificar un permiso específico
```blade
@can('users.create')
    <a href="{{ route('users.create') }}" class="btn btn-primary">Crear Usuario</a>
@endcan

@can('sales.view')
    <li><a href="{{ route('sales.index') }}">Ventas</a></li>
@else
    <li class="disabled">Ventas (Sin acceso)</li>
@endcan
```

#### 2. @canany - Verificar si tiene alguno de varios permisos
```blade
@canany(['users.edit', 'users.delete'])
    <div class="admin-actions">
        @can('users.edit')
            <button onclick="editUser()">Editar</button>
        @endcan
        @can('users.delete')
            <button onclick="deleteUser()">Eliminar</button>
        @endcan
    </div>
@endcanany
```

#### 3. @canall - Verificar si tiene todos los permisos
```blade
@canall(['sales.create', 'products.view', 'customers.view'])
    <a href="{{ route('sales.create') }}">Nueva Venta</a>
@endcanall
```

#### 4. @role - Verificar si tiene un rol específico
```blade
@role('contador')
    <a href="{{ route('journal-entries.index') }}">Asientos Contables</a>
@endrole
```

#### 5. @hasanyrole - Verificar si tiene alguno de varios roles
```blade
@hasanyrole(['administrador', 'contador'])
    <li><a href="{{ route('account-chart.index') }}">Plan de Cuentas</a></li>
@endhasanyrole
```

### Funciones Helper Globales

#### 1. user_can() - Verificar permiso en PHP
```php
// En controladores
public function index()
{
    if (user_can('sales.view')) {
        return view('sales.index');
    }
    abort(403);
}

// En vistas
@if(user_can('users.create'))
    <button>Nuevo Usuario</button>
@endif
```

#### 2. user_can_any() - Verificar alguno de varios permisos
```php
if (user_can_any(['sales.create', 'sales.edit'])) {
    // Mostrar formulario
}
```

#### 3. user_can_all() - Verificar todos los permisos
```php
if (user_can_all(['products.view', 'customers.view', 'sales.create'])) {
    // Permitir crear venta
}
```

#### 4. abort_unless_can() - Abortar si no tiene permiso
```php
public function destroy(User $user)
{
    abort_unless_can('users.delete', 'No puedes eliminar usuarios.');

    $user->delete();
    return response()->json(['success' => true]);
}
```

#### 5. user_has_role() - Verificar rol
```php
if (user_has_role('contador')) {
    // Código específico para contador
}
```

#### 6. user_permissions() - Obtener todos los permisos del usuario
```php
$permissions = user_permissions();
// Retorna Collection de Permission
```

#### 7. user_permission_slugs() - Obtener array de slugs de permisos
```php
$slugs = user_permission_slugs();
// Retorna ['users.view', 'sales.create', ...]
```

---

## 👥 Roles Predefinidos Creados

### 1. Administrador
- **Slug**: `administrador`
- **Permisos**: TODOS (acceso completo al sistema)
- **Uso**: Usuario con control total del sistema

### 2. Contador
- **Slug**: `contador`
- **Módulos**: Contabilidad, bancos, cuentas por cobrar/pagar, gastos, reportes
- **Permisos adicionales**: Ver clientes, proveedores y productos
- **Uso**: Personal contable y financiero

### 3. Cajero
- **Slug**: `cajero`
- **Módulos**: Ventas, caja, notas de crédito
- **Permisos**: Crear/ver ventas, abrir/cerrar caja, registrar pagos
- **Uso**: Personal de punto de venta

### 4. Vendedor
- **Slug**: `vendedor`
- **Módulos**: Ventas, remisiones, clientes
- **Permisos**: Crear ventas y remisiones, gestionar clientes
- **Uso**: Equipo de ventas

### 5. Almacenero
- **Slug**: `almacenero`
- **Módulos**: Productos, inventario, compras, proveedores
- **Permisos**: Gestión completa de inventario y compras
- **Uso**: Personal de bodega y almacén

### 6. Supervisor de Ventas
- **Slug**: `supervisor-ventas`
- **Módulos**: Ventas, remisiones, notas de crédito, clientes, cuentas por cobrar
- **Permisos**: Incluye anulaciones y supervisión completa de ventas
- **Uso**: Jefe de ventas o supervisor

---

## 📝 Notas Importantes

1. **Super Admin siempre tiene acceso**: No necesitas asignar permisos al super admin.

2. **Rutas `list` y `data`**: Estas rutas son auxiliares (para combos y datatables). Generalmente deben tener el mismo permiso que la ruta `index` o `view`.

3. **Permisos en cadena**: Si un usuario tiene permiso para "crear ventas" pero no para "ver productos", no podrá crear ventas correctamente. Asegúrate de dar permisos relacionados.

4. **Método rápido para probar**: Crea un usuario de prueba, asígnale solo ciertos permisos y verifica que las rutas se bloqueen correctamente.

---

## 🔍 Verificar que los Permisos Funcionan

### 1. Verificar permisos en base de datos
```sql
SELECT m.module, COUNT(*) as total_permisos
FROM permissions p
JOIN (SELECT DISTINCT module FROM permissions) m ON p.module = m.module
GROUP BY m.module
ORDER BY m.module;
```

### 2. Verificar middleware registrado
```bash
php artisan route:list | grep permission
```

### 3. Probar con usuario sin permisos
- Crear usuario de prueba
- NO asignarle ningún rol
- Intentar acceder a una ruta protegida
- Debe redirigir con mensaje de error

---

## 🔧 Pasos para Activar el Sistema de Permisos

### 1. Ejecutar el Seeder de Permisos
```bash
php artisan db:seed --class=PermissionSeeder
```
Este comando creará o actualizará los **168 permisos** en la base de datos.

### 2. Ejecutar el Seeder de Roles
```bash
php artisan db:seed --class=RolesSeeder
```
Este comando creará los **6 roles predefinidos** y asignará los permisos correspondientes a cada rol.

### 3. Regenerar Autoload de Composer
```bash
composer dump-autoload
```
Este comando asegura que las funciones helper estén disponibles en toda la aplicación.

### 4. Asignar Roles a Usuarios
Desde la interfaz de usuarios en el sistema, asigna los roles apropiados a cada usuario.

O desde artisan tinker:
```php
php artisan tinker

$user = User::find(1);
$rol = Role::where('slug', 'contador')->first();
$user->roles()->attach($rol->id);
```

### 5. Probar el Sistema
- Crea un usuario de prueba
- Asígnale un rol (por ejemplo, "Cajero")
- Inicia sesión con ese usuario
- Verifica que solo vea los módulos y botones permitidos

---

## 📧 Soporte

Para dudas sobre la implementación de permisos, consultar:
- Documentación de Laravel: https://laravel.com/docs/authorization
- Archivo de configuración: `config/auth.php`
- Modelos: `app/Models/Role.php`, `app/Models/Permission.php`, `app/Models/User.php`
- Helper de permisos: `app/Helpers/PermissionHelper.php`
- Directivas Blade: `app/Providers/AppServiceProvider.php` (método `registerBladeDirectives()`)
