<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(?int $tenantId = null): void
    {
        // Si no se pasa tenantId, asumimos ejecución global (instalación inicial)
        // O podríamos decidir solo correr para un tenant específico si se pasa.

        if ($tenantId === null) {
            // Lógica original para Super Admin solo si es ejecución global
            $superAdmin = Role::updateOrCreate(
                ['slug' => 'super-admin', 'tenant_id' => null],
                [
                    'name' => 'Super Administrador',
                    'description' => 'Acceso total al sistema',
                    'is_system' => true,
                ]
            );
            $superAdmin->permissions()->sync(Permission::pluck('id'));

            // Crear usuario super admin por defecto
            $user = User::updateOrCreate(
                ['email' => 'admin@neoerp.com'],
                [
                    'tenant_id' => null,
                    'name' => 'Administrador',
                    'password' => Hash::make('password'), // Debería cambiarse en producción
                    'is_active' => true,
                ]
            );
            $user->roles()->sync([$superAdmin->id]);

            // Si hay un tenant por defecto (id 1), crear sus roles también
            $defaultTenant = Tenant::find(1);
            if ($defaultTenant) {
                $this->createTenantRoles($defaultTenant->id);
            }

        } else {
            // Ejecución específica para un tenant
            $this->createTenantRoles($tenantId);
        }
    }

    private function createTenantRoles(int $tenantId): void
    {
        // Crear rol Admin para el tenant
        $admin = Role::updateOrCreate(
            ['slug' => 'admin', 'tenant_id' => $tenantId],
            [
                'name' => 'Administrador',
                'description' => 'Administrador de la empresa',
                'is_system' => true,
            ]
        );

        // Asignar permisos al admin (excepto configuración global)
        $adminPermissions = Permission::where('module', '!=', 'configuracion')->pluck('id');
        $admin->permissions()->sync($adminPermissions);

        // Crear rol Vendedor
        $vendedor = Role::updateOrCreate(
            ['slug' => 'vendedor', 'tenant_id' => $tenantId],
            [
                'name' => 'Vendedor',
                'description' => 'Usuario con permisos de ventas',
                'is_system' => true,
            ]
        );

        // Permisos del vendedor
        $vendedorPermissions = Permission::whereIn('slug', [
            'customers.view',
            'customers.create',
            'customers.edit',
            'products.view',
            'sales.view',
            'sales.create',
        ])->pluck('id');
        $vendedor->permissions()->sync($vendedorPermissions);
    }
}
