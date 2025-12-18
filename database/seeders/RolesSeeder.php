<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador - Acceso completo al sistema
        $admin = Role::updateOrCreate(
            ['slug' => 'administrador'],
            [
                'name' => 'Administrador',
                'description' => 'Acceso completo a todos los módulos del sistema',
                'is_system' => true,
            ]
        );

        // Asignar todos los permisos
        $allPermissions = Permission::all()->pluck('id');
        $admin->permissions()->sync($allPermissions);

        // 2. Contador - Módulos contables y financieros
        $contador = Role::updateOrCreate(
            ['slug' => 'contador'],
            [
                'name' => 'Contador',
                'description' => 'Gestión contable, financiera y bancaria completa',
                'is_system' => false,
            ]
        );

        $contadorPermisos = Permission::whereIn('module', [
            'contabilidad',
            'asientos_contables',
            'libro_mayor',
            'estados_financieros',
            'bancos',
            'cuentas_bancarias',
            'transacciones_bancarias',
            'cheques',
            'conciliacion_bancaria',
            'cuentas_cobrar',
            'cuentas_pagar',
            'gastos',
            'reportes',
        ])->orWhereIn('slug', [
            // Necesita ver clientes y proveedores
            'customers.view',
            'suppliers.view',
            // Necesita ver productos para reportes
            'products.view',
        ])->pluck('id');

        $contador->permissions()->sync($contadorPermisos);

        // 3. Cajero - Ventas, caja y operaciones diarias
        $cajero = Role::updateOrCreate(
            ['slug' => 'cajero'],
            [
                'name' => 'Cajero',
                'description' => 'Manejo de caja, ventas y cobros',
                'is_system' => false,
            ]
        );

        $cajeroPermisos = Permission::whereIn('slug', [
            // Ventas
            'sales.view',
            'sales.create',
            // Caja
            'cash-register.view',
            'cash-register.open',
            'cash-register.close',
            'cash-register.movements',
            // Clientes (ver y crear)
            'customers.view',
            'customers.create',
            // Productos (solo ver)
            'products.view',
            // Cuentas por cobrar (ver y registrar pagos)
            'accounts-receivable.view',
            'accounts-receivable.payment',
            // Notas de crédito (ver y crear)
            'credit-notes.view',
            'credit-notes.create',
        ])->pluck('id');

        $cajero->permissions()->sync($cajeroPermisos);

        // 4. Vendedor - Ventas y gestión de clientes
        $vendedor = Role::updateOrCreate(
            ['slug' => 'vendedor'],
            [
                'name' => 'Vendedor',
                'description' => 'Gestión de ventas, clientes y remisiones',
                'is_system' => false,
            ]
        );

        $vendedorPermisos = Permission::whereIn('slug', [
            // Ventas
            'sales.view',
            'sales.create',
            // Remisiones
            'remissions.view',
            'remissions.create',
            'remissions.confirm',
            'remissions.deliver',
            'remissions.convert',
            // Clientes (completo)
            'customers.view',
            'customers.create',
            'customers.edit',
            // Productos (solo ver)
            'products.view',
            // Notas de crédito (ver)
            'credit-notes.view',
            // Cuentas por cobrar (solo ver)
            'accounts-receivable.view',
        ])->pluck('id');

        $vendedor->permissions()->sync($vendedorPermisos);

        // 5. Almacenero - Inventario, productos y compras
        $almacenero = Role::updateOrCreate(
            ['slug' => 'almacenero'],
            [
                'name' => 'Almacenero',
                'description' => 'Gestión de inventario, productos y compras',
                'is_system' => false,
            ]
        );

        $almaceneroPermisos = Permission::whereIn('slug', [
            // Productos
            'products.view',
            'products.create',
            'products.edit',
            // Categorías
            'categories.view',
            'categories.create',
            'categories.edit',
            // Inventario
            'inventory-adjustments.view',
            'inventory-adjustments.create',
            'inventory-adjustments.confirm',
            // Compras
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            // Proveedores
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            // Cuentas por pagar (solo ver)
            'accounts-payable.view',
        ])->pluck('id');

        $almacenero->permissions()->sync($almaceneroPermisos);

        // 6. Supervisor de Ventas - Ventas completas + anulaciones
        $supervisorVentas = Role::updateOrCreate(
            ['slug' => 'supervisor-ventas'],
            [
                'name' => 'Supervisor de Ventas',
                'description' => 'Supervisión y gestión completa de ventas, puede anular documentos',
                'is_system' => false,
            ]
        );

        $supervisorVentasPermisos = Permission::whereIn('module', [
            'ventas',
            'notas_credito',
            'remisiones',
            'clientes',
            'cuentas_cobrar',
        ])->orWhereIn('slug', [
            'products.view',
            'cash-register.view',
            'reportes.view',
        ])->pluck('id');

        $supervisorVentas->permissions()->sync($supervisorVentasPermisos);
    }
}
