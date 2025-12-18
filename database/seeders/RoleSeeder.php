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
    public function run(): void
    {
        $tenant = Tenant::first();
        
        // Crear rol Super Admin (sin tenant - acceso global)
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'tenant_id' => null,
                'name' => 'Super Administrador',
                'description' => 'Acceso total al sistema',
                'is_system' => true,
            ]
        );
        
        // Asignar todos los permisos al super admin
        $superAdmin->permissions()->sync(Permission::pluck('id'));
        
        // Crear rol Admin para el tenant
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'tenant_id' => $tenant?->id,
                'name' => 'Administrador',
                'description' => 'Administrador de la empresa',
                'is_system' => true,
            ]
        );
        
        // Asignar todos los permisos al admin (excepto configuración global)
        $adminPermissions = Permission::where('module', '!=', 'configuracion')->pluck('id');
        $admin->permissions()->sync($adminPermissions);
        
        // Crear rol Vendedor
        $vendedor = Role::updateOrCreate(
            ['slug' => 'vendedor'],
            [
                'tenant_id' => $tenant?->id,
                'name' => 'Vendedor',
                'description' => 'Usuario con permisos de ventas',
                'is_system' => true,
            ]
        );
        
        // Permisos del vendedor
        $vendedorPermissions = Permission::whereIn('slug', [
            'customers.view', 'customers.create', 'customers.edit',
            'products.view',
            'sales.view', 'sales.create',
        ])->pluck('id');
        $vendedor->permissions()->sync($vendedorPermissions);
        
        // Crear usuario super admin
        $user = User::updateOrCreate(
            ['email' => 'admin@neoerp.com'],
            [
                'tenant_id' => $tenant?->id,
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        
        $user->roles()->sync([$superAdmin->id]);
    }
}
