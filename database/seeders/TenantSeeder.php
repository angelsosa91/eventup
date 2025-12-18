<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['ruc' => '80000000-0'],
            [
                'name' => 'Empresa Demo',
                'email' => 'demo@neoerp.com',
                'phone' => '+595 21 000 000',
                'address' => 'Dirección Demo 123',
                'city' => 'Asunción',
                'country' => 'Paraguay',
                'is_active' => true,
            ]
        );
    }
}
