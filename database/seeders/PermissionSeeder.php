<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Usuarios
            ['name' => 'Ver Usuarios', 'slug' => 'users.view', 'module' => 'usuarios'],
            ['name' => 'Crear Usuarios', 'slug' => 'users.create', 'module' => 'usuarios'],
            ['name' => 'Editar Usuarios', 'slug' => 'users.edit', 'module' => 'usuarios'],
            ['name' => 'Eliminar Usuarios', 'slug' => 'users.delete', 'module' => 'usuarios'],
            
            // Roles
            ['name' => 'Ver Roles', 'slug' => 'roles.view', 'module' => 'roles'],
            ['name' => 'Crear Roles', 'slug' => 'roles.create', 'module' => 'roles'],
            ['name' => 'Editar Roles', 'slug' => 'roles.edit', 'module' => 'roles'],
            ['name' => 'Eliminar Roles', 'slug' => 'roles.delete', 'module' => 'roles'],
            
            // Clientes
            ['name' => 'Ver Clientes', 'slug' => 'customers.view', 'module' => 'clientes'],
            ['name' => 'Crear Clientes', 'slug' => 'customers.create', 'module' => 'clientes'],
            ['name' => 'Editar Clientes', 'slug' => 'customers.edit', 'module' => 'clientes'],
            ['name' => 'Eliminar Clientes', 'slug' => 'customers.delete', 'module' => 'clientes'],
            
            // Proveedores
            ['name' => 'Ver Proveedores', 'slug' => 'suppliers.view', 'module' => 'proveedores'],
            ['name' => 'Crear Proveedores', 'slug' => 'suppliers.create', 'module' => 'proveedores'],
            ['name' => 'Editar Proveedores', 'slug' => 'suppliers.edit', 'module' => 'proveedores'],
            ['name' => 'Eliminar Proveedores', 'slug' => 'suppliers.delete', 'module' => 'proveedores'],
            
            // Productos
            ['name' => 'Ver Productos', 'slug' => 'products.view', 'module' => 'productos'],
            ['name' => 'Crear Productos', 'slug' => 'products.create', 'module' => 'productos'],
            ['name' => 'Editar Productos', 'slug' => 'products.edit', 'module' => 'productos'],
            ['name' => 'Eliminar Productos', 'slug' => 'products.delete', 'module' => 'productos'],
            
            // Ventas
            ['name' => 'Ver Ventas', 'slug' => 'sales.view', 'module' => 'ventas'],
            ['name' => 'Crear Ventas', 'slug' => 'sales.create', 'module' => 'ventas'],
            ['name' => 'Anular Ventas', 'slug' => 'sales.cancel', 'module' => 'ventas'],

            // Notas de Crédito
            ['name' => 'Ver Notas de Crédito', 'slug' => 'credit-notes.view', 'module' => 'notas_credito'],
            ['name' => 'Crear Notas de Crédito', 'slug' => 'credit-notes.create', 'module' => 'notas_credito'],
            ['name' => 'Confirmar Notas de Crédito', 'slug' => 'credit-notes.confirm', 'module' => 'notas_credito'],
            ['name' => 'Anular Notas de Crédito', 'slug' => 'credit-notes.cancel', 'module' => 'notas_credito'],

            // Remisiones
            ['name' => 'Ver Remisiones', 'slug' => 'remissions.view', 'module' => 'remisiones'],
            ['name' => 'Crear Remisiones', 'slug' => 'remissions.create', 'module' => 'remisiones'],
            ['name' => 'Confirmar Remisiones', 'slug' => 'remissions.confirm', 'module' => 'remisiones'],
            ['name' => 'Entregar Remisiones', 'slug' => 'remissions.deliver', 'module' => 'remisiones'],
            ['name' => 'Convertir Remisiones a Factura', 'slug' => 'remissions.convert', 'module' => 'remisiones'],
            ['name' => 'Anular Remisiones', 'slug' => 'remissions.cancel', 'module' => 'remisiones'],

            // Compras
            ['name' => 'Ver Compras', 'slug' => 'purchases.view', 'module' => 'compras'],
            ['name' => 'Crear Compras', 'slug' => 'purchases.create', 'module' => 'compras'],
            ['name' => 'Editar Compras', 'slug' => 'purchases.edit', 'module' => 'compras'],
            ['name' => 'Anular Compras', 'slug' => 'purchases.cancel', 'module' => 'compras'],
            
            // Gastos
            ['name' => 'Ver Gastos', 'slug' => 'expenses.view', 'module' => 'gastos'],
            ['name' => 'Crear Gastos', 'slug' => 'expenses.create', 'module' => 'gastos'],
            ['name' => 'Editar Gastos', 'slug' => 'expenses.edit', 'module' => 'gastos'],
            ['name' => 'Eliminar Gastos', 'slug' => 'expenses.delete', 'module' => 'gastos'],
            
            // Reportes
            ['name' => 'Ver Reportes', 'slug' => 'reports.view', 'module' => 'reportes'],

            // Configuración
            ['name' => 'Configuración General', 'slug' => 'settings.general', 'module' => 'configuracion'],

            // Inventario
            ['name' => 'Ver Ajustes de Inventario', 'slug' => 'inventory-adjustments.view', 'module' => 'inventario'],
            ['name' => 'Crear Ajustes de Inventario', 'slug' => 'inventory-adjustments.create', 'module' => 'inventario'],
            ['name' => 'Confirmar Ajustes de Inventario', 'slug' => 'inventory-adjustments.confirm', 'module' => 'inventario'],
            ['name' => 'Anular Ajustes de Inventario', 'slug' => 'inventory-adjustments.cancel', 'module' => 'inventario'],

            // Cuentas por Cobrar
            ['name' => 'Ver Cuentas por Cobrar', 'slug' => 'accounts-receivable.view', 'module' => 'cuentas_cobrar'],
            ['name' => 'Crear Cuentas por Cobrar', 'slug' => 'accounts-receivable.create', 'module' => 'cuentas_cobrar'],
            ['name' => 'Editar Cuentas por Cobrar', 'slug' => 'accounts-receivable.edit', 'module' => 'cuentas_cobrar'],
            ['name' => 'Registrar Pagos', 'slug' => 'accounts-receivable.payment', 'module' => 'cuentas_cobrar'],
            ['name' => 'Anular Cuentas por Cobrar', 'slug' => 'accounts-receivable.cancel', 'module' => 'cuentas_cobrar'],

            // Cuentas por Pagar
            ['name' => 'Ver Cuentas por Pagar', 'slug' => 'accounts-payable.view', 'module' => 'cuentas_pagar'],
            ['name' => 'Crear Cuentas por Pagar', 'slug' => 'accounts-payable.create', 'module' => 'cuentas_pagar'],
            ['name' => 'Editar Cuentas por Pagar', 'slug' => 'accounts-payable.edit', 'module' => 'cuentas_pagar'],
            ['name' => 'Registrar Pagos', 'slug' => 'accounts-payable.payment', 'module' => 'cuentas_pagar'],
            ['name' => 'Anular Cuentas por Pagar', 'slug' => 'accounts-payable.cancel', 'module' => 'cuentas_pagar'],

            // Caja
            ['name' => 'Ver Caja', 'slug' => 'cash-register.view', 'module' => 'caja'],
            ['name' => 'Abrir Caja', 'slug' => 'cash-register.open', 'module' => 'caja'],
            ['name' => 'Cerrar Caja', 'slug' => 'cash-register.close', 'module' => 'caja'],
            ['name' => 'Movimientos de Caja', 'slug' => 'cash-register.movements', 'module' => 'caja'],

            // Bancos - Catálogo
            ['name' => 'Ver Bancos', 'slug' => 'banks.view', 'module' => 'bancos'],
            ['name' => 'Crear Bancos', 'slug' => 'banks.create', 'module' => 'bancos'],
            ['name' => 'Editar Bancos', 'slug' => 'banks.edit', 'module' => 'bancos'],
            ['name' => 'Eliminar Bancos', 'slug' => 'banks.delete', 'module' => 'bancos'],

            // Cuentas Bancarias
            ['name' => 'Ver Cuentas Bancarias', 'slug' => 'bank-accounts.view', 'module' => 'cuentas_bancarias'],
            ['name' => 'Crear Cuentas Bancarias', 'slug' => 'bank-accounts.create', 'module' => 'cuentas_bancarias'],
            ['name' => 'Editar Cuentas Bancarias', 'slug' => 'bank-accounts.edit', 'module' => 'cuentas_bancarias'],
            ['name' => 'Activar/Desactivar Cuentas', 'slug' => 'bank-accounts.toggle-status', 'module' => 'cuentas_bancarias'],

            // Transacciones Bancarias
            ['name' => 'Ver Transacciones Bancarias', 'slug' => 'bank-transactions.view', 'module' => 'transacciones_bancarias'],
            ['name' => 'Crear Transacciones Bancarias', 'slug' => 'bank-transactions.create', 'module' => 'transacciones_bancarias'],
            ['name' => 'Realizar Transferencias', 'slug' => 'bank-transactions.transfer', 'module' => 'transacciones_bancarias'],
            ['name' => 'Depósitos desde Caja', 'slug' => 'bank-transactions.cash-deposit', 'module' => 'transacciones_bancarias'],
            ['name' => 'Retiros a Caja', 'slug' => 'bank-transactions.cash-withdrawal', 'module' => 'transacciones_bancarias'],
            ['name' => 'Anular Transacciones', 'slug' => 'bank-transactions.cancel', 'module' => 'transacciones_bancarias'],

            // Cheques
            ['name' => 'Ver Cheques', 'slug' => 'checks.view', 'module' => 'cheques'],
            ['name' => 'Crear Cheques', 'slug' => 'checks.create', 'module' => 'cheques'],
            ['name' => 'Depositar Cheques', 'slug' => 'checks.deposit', 'module' => 'cheques'],
            ['name' => 'Cobrar Cheques', 'slug' => 'checks.cash', 'module' => 'cheques'],
            ['name' => 'Rechazar Cheques', 'slug' => 'checks.bounce', 'module' => 'cheques'],
            ['name' => 'Anular Cheques', 'slug' => 'checks.cancel', 'module' => 'cheques'],

            // Conciliación Bancaria
            ['name' => 'Ver Conciliaciones Bancarias', 'slug' => 'bank-reconciliations.view', 'module' => 'conciliacion_bancaria'],
            ['name' => 'Crear Conciliaciones Bancarias', 'slug' => 'bank-reconciliations.create', 'module' => 'conciliacion_bancaria'],
            ['name' => 'Editar Conciliaciones Bancarias', 'slug' => 'bank-reconciliations.edit', 'module' => 'conciliacion_bancaria'],
            ['name' => 'Publicar Conciliaciones', 'slug' => 'bank-reconciliations.post', 'module' => 'conciliacion_bancaria'],
            ['name' => 'Cancelar Conciliaciones', 'slug' => 'bank-reconciliations.cancel', 'module' => 'conciliacion_bancaria'],
            ['name' => 'Eliminar Conciliaciones', 'slug' => 'bank-reconciliations.delete', 'module' => 'conciliacion_bancaria'],

            // Contabilidad - Plan de Cuentas
            ['name' => 'Ver Plan de Cuentas', 'slug' => 'account-chart.view', 'module' => 'contabilidad'],
            ['name' => 'Crear Cuentas', 'slug' => 'account-chart.create', 'module' => 'contabilidad'],
            ['name' => 'Editar Cuentas', 'slug' => 'account-chart.edit', 'module' => 'contabilidad'],
            ['name' => 'Eliminar Cuentas', 'slug' => 'account-chart.delete', 'module' => 'contabilidad'],

            // Asientos Contables
            ['name' => 'Ver Asientos Contables', 'slug' => 'journal-entries.view', 'module' => 'asientos_contables'],
            ['name' => 'Crear Asientos Contables', 'slug' => 'journal-entries.create', 'module' => 'asientos_contables'],
            ['name' => 'Editar Asientos Contables', 'slug' => 'journal-entries.edit', 'module' => 'asientos_contables'],
            ['name' => 'Publicar Asientos', 'slug' => 'journal-entries.post', 'module' => 'asientos_contables'],
            ['name' => 'Anular Asientos', 'slug' => 'journal-entries.cancel', 'module' => 'asientos_contables'],

            // Libro Mayor
            ['name' => 'Ver Libro Mayor', 'slug' => 'general-ledger.view', 'module' => 'libro_mayor'],
            ['name' => 'Exportar Libro Mayor', 'slug' => 'general-ledger.export', 'module' => 'libro_mayor'],

            // Estados Financieros
            ['name' => 'Ver Balance General', 'slug' => 'financial-statements.balance-sheet', 'module' => 'estados_financieros'],
            ['name' => 'Ver Estado de Resultados', 'slug' => 'financial-statements.income-statement', 'module' => 'estados_financieros'],
            ['name' => 'Ver Balance de Comprobación', 'slug' => 'financial-statements.trial-balance', 'module' => 'estados_financieros'],

            // Categorías
            ['name' => 'Ver Categorías', 'slug' => 'categories.view', 'module' => 'categorias'],
            ['name' => 'Crear Categorías', 'slug' => 'categories.create', 'module' => 'categorias'],
            ['name' => 'Editar Categorías', 'slug' => 'categories.edit', 'module' => 'categorias'],
            ['name' => 'Eliminar Categorías', 'slug' => 'categories.delete', 'module' => 'categorias'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
