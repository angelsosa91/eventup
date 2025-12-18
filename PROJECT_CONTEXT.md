# NEO ERP - Contexto del Proyecto

> Este archivo contiene toda la información necesaria para continuar el desarrollo en futuras sesiones sin necesidad de re-explicar el contexto.

---

## Resumen Ejecutivo

**Neo ERP** es un sistema de planificación de recursos empresariales (ERP) desarrollado en Laravel 12 con arquitectura multi-tenant. El sistema está diseñado para gestionar usuarios, roles, clientes, proveedores, categorías y productos con aislamiento de datos por empresa.

---

## Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|------------|---------|
| Backend | Laravel | 12.x |
| PHP | | 8.2+ |
| Frontend | Blade + jEasyUI + Bootstrap 5 | 5.3.2 |
| CSS | Tailwind CSS | 4.0 |
| Build Tool | Vite | 7.x |
| Base de Datos | SQLite | - |
| HTTP Client | Axios | 1.11.0 |

---

## Arquitectura Multi-Tenant

El sistema utiliza aislamiento por `tenant_id`:

```php
// Trait: app/Traits/BelongsToTenant.php
- Asigna automáticamente tenant_id al crear registros
- Filtra automáticamente por tenant del usuario autenticado
- Aplicado a: Customer, Supplier, Product, Category
```

---

## Estructura de Base de Datos

### Tablas Principales

| Tabla | Descripción | Campos Clave |
|-------|-------------|--------------|
| `tenants` | Empresas/Organizaciones | name, ruc, email, is_active |
| `users` | Usuarios del sistema | tenant_id, name, email, is_active |
| `roles` | Roles de usuario | tenant_id, name, slug, is_system |
| `permissions` | Permisos del sistema | name, slug, module |
| `customers` | Clientes | tenant_id, name, ruc, credit_limit |
| `suppliers` | Proveedores | tenant_id, name, ruc, payment_days |
| `categories` | Categorías de productos | tenant_id, name, code |
| `products` | Inventario | tenant_id, category_id, code, stock, prices |

### Relaciones

```
Tenant (1) ──── (N) Users
Tenant (1) ──── (N) Customers/Suppliers/Categories/Products
User (N) ──── (N) Roles (pivot: role_user)
Role (N) ──── (N) Permissions (pivot: permission_role)
Category (1) ──── (N) Products
```

---

## Módulos Implementados

### 1. Autenticación
- **Controller:** `Auth/LoginController.php`
- **Rutas:** GET/POST `/login`, POST `/logout`
- **Características:** Login con email/password, verificación de usuario activo

### 2. Dashboard
- **Controller:** `DashboardController.php`
- **Ruta:** GET `/dashboard`
- **Estadísticas:** Ventas del día, clientes, productos, proveedores

### 3. Gestión de Usuarios
- **Controller:** `UserController.php`
- **Vista:** `resources/views/users/index.blade.php`
- **Rutas:** CRUD completo + toggle-status

### 4. Gestión de Roles
- **Controller:** `RoleController.php`
- **Vista:** `resources/views/roles/index.blade.php`
- **Características:** Asignación de permisos, protección de roles de sistema

### 5. Gestión de Clientes
- **Controller:** `CustomerController.php`
- **Vista:** `resources/views/customers/index.blade.php`
- **Campos:** Límite de crédito, días de crédito

### 6. Gestión de Proveedores
- **Controller:** `SupplierController.php`
- **Vista:** `resources/views/suppliers/index.blade.php`
- **Campos:** Datos bancarios, días de pago, persona de contacto

### 7. Gestión de Categorías
- **Controller:** `CategoryController.php`
- **Vista:** `resources/views/categories/index.blade.php`

### 8. Gestión de Productos
- **Controller:** `ProductController.php`
- **Vista:** `resources/views/products/index.blade.php`
- **Campos:** Stock, precios compra/venta, código de barras

---

## Permisos del Sistema (35 total)

```
usuarios:    users.view, users.create, users.edit, users.delete
roles:       roles.view, roles.create, roles.edit, roles.delete
clientes:    customers.view, customers.create, customers.edit, customers.delete
proveedores: suppliers.view, suppliers.create, suppliers.edit, suppliers.delete
productos:   products.view, products.create, products.edit, products.delete
ventas:      sales.view, sales.create, sales.cancel
compras:     purchases.view, purchases.create, purchases.edit, purchases.cancel
gastos:      expenses.view, expenses.create, expenses.edit, expenses.delete
reportes:    reports.view
config:      settings.general
```

---

## Roles por Defecto

| Rol | Slug | Permisos |
|-----|------|----------|
| Super Admin | `super-admin` | Todos |
| Admin | `admin` | Todos excepto configuración |
| Vendedor | `vendedor` | Clientes (ver/crear/editar), Productos (ver), Ventas (ver/crear) |

---

## Patrones de Código

### Estructura de Controllers

```php
class ResourceController extends Controller
{
    public function index()     // Retorna vista HTML
    public function data()      // JSON para DataGrid (paginado)
    public function list()      // JSON para select/combobox
    public function store()     // Crear (POST)
    public function show($id)   // Obtener uno (GET)
    public function update($id) // Actualizar (PUT)
    public function destroy($id)// Eliminar (DELETE)
}
```

### Respuestas API

```php
// Éxito
return response()->json([
    'success' => true,
    'message' => 'Mensaje',
    'data' => $model
]);

// DataGrid
return response()->json([
    'total' => $total,
    'rows' => $items
]);

// Error de validación (422)
return response()->json(['errors' => $validator->errors()], 422);
```

### Query con Paginación

```php
$query = Model::query()
    ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
    ->orderBy($sort, $order);

$total = $query->count();
$items = $query->skip(($page - 1) * $rows)->take($rows)->get();
```

---

## Frontend (jEasyUI + Bootstrap)

### Estructura de Vistas

```html
@extends('layouts.app')
@section('title', 'Título')
@section('content')
    <!-- Toolbar con botones -->
    <!-- DataGrid -->
    <!-- Modal de formulario -->
    <script>
        // Funciones: newItem(), editItem(), deleteItem(), saveItem(), doSearch()
    </script>
@endsection
```

### Layout Principal
- **Archivo:** `resources/views/layouts/app.blade.php`
- **Sidebar:** Navegación fija a la izquierda
- **Contenido:** Área principal con título de página

---

## Datos de Prueba

```
Tenant: Empresa Demo (RUC: 80000000-0)
Usuario: admin@neoerp.com
Password: password
```

---

## Módulos Pendientes (Permisos ya existen)

- [ ] **Ventas** - sales.view, sales.create, sales.cancel
- [ ] **Compras** - purchases.view, purchases.create, purchases.edit, purchases.cancel
- [ ] **Gastos** - expenses.view, expenses.create, expenses.edit, expenses.delete
- [ ] **Reportes** - reports.view
- [ ] **Configuración** - settings.general

---

## Comandos Útiles

```bash
# Servidor de desarrollo
php artisan serve

# Compilar assets
npm run dev

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Convenciones

1. **Idioma UI:** Español
2. **Formato moneda:** Europeo (1.234,50)
3. **Validación:** Server-side con Laravel Validator
4. **Eliminación:** Soft-validation (no se eliminan registros con relaciones)
5. **Tenant:** Automático via trait BelongsToTenant

---

## Archivos Clave

```
Rutas:           routes/web.php
Layout:          resources/views/layouts/app.blade.php
Modelos:         app/Models/
Controllers:     app/Http/Controllers/
Migraciones:     database/migrations/
Seeders:         database/seeders/
Trait Tenant:    app/Traits/BelongsToTenant.php
Config:          .env, config/
```

---

## Notas para Desarrollo Futuro

1. **Ventas/Compras:** Necesitarán tablas para documentos (sales, purchases) y detalles (sale_items, purchase_items)
2. **Inventario:** Considerar movimientos de stock al crear ventas/compras
3. **Reportes:** Dashboard más completo con gráficos
4. **Configuración:** Ajustes de empresa, moneda, impuestos

---

*Última actualización: 2025-11-24*
