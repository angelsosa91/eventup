<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\AccountChartSeeder;
use Database\Seeders\AccountingSettingSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                            {name : El nombre de la empresa} 
                            {email : El email del usuario administrador}
                            {password : La contraseña del usuario}
                            {--ruc= : RUC de la empresa (opcional)}
                            {--phone= : Teléfono de la empresa (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un nuevo tenant (empresa) con su usuario admin y configuraciones iniciales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        $ruc = $this->option('ruc');
        $phone = $this->option('phone');

        $this->info("Creando empresa: {$name}...");

        DB::beginTransaction();

        try {
            // 1. Crear el Tenant
            $tenant = Tenant::create([
                'name' => $name,
                'ruc' => $ruc,
                'email' => $email, // Usamos el email del admin como contacto principal
                'phone' => $phone,
                'is_active' => true,
            ]);

            $this->info("Tenant creado con ID: {$tenant->id}");

            // 2. Ejecutar RoleSeeder para crear roles 'admin' y 'vendedor' para este tenant
            $this->info("Generando roles...");
            $roleSeeder = new RoleSeeder();
            $roleSeeder->run($tenant->id);

            // 3. Crear el Usuario Administrador
            $this->info("Creando usuario administrador...");
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => 'Administrador',
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => true,
            ]);

            // 4. Asignar rol 'admin' al usuario
            // Buscamos el rol 'admin' que acabamos de crear para ESTE tenant
            $adminRole = $tenant->roles()->where('slug', 'admin')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
                $this->info("Rol 'admin' asignado al usuario.");
            } else {
                $this->error("No se pudo encontrar el rol 'admin' para asignar.");
                throw new \Exception("Rol admin no encontrado");
            }

            // 5. Ejecutar Seeders de Contabilidad
            $this->info("Generando plan de cuentas...");
            $chartSeeder = new AccountChartSeeder();
            $chartSeeder->run($tenant->id);

            $this->info("Configurando asientos contables...");
            $settingSeeder = new AccountingSettingSeeder();
            $settingSeeder->run($tenant->id);

            DB::commit();

            $this->info("------------------------------------------------");
            $this->info("¡Empresa creada exitosamente!");
            $this->info("Nombre: {$tenant->name}");
            $this->info("Admin: {$user->email}");
            $this->info("------------------------------------------------");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error al crear la empresa: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
