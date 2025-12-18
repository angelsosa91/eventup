#!/bin/bash

# Script para agregar un nuevo tenant/cliente
# Uso: ./scripts/add-tenant.sh "Empresa XYZ" "admin@empresa.com" "password123"

set -e

TENANT_NAME=$1
ADMIN_EMAIL=$2
ADMIN_PASSWORD=$3

if [ -z "$TENANT_NAME" ] || [ -z "$ADMIN_EMAIL" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "❌ Uso: ./scripts/add-tenant.sh \"Nombre Empresa\" \"admin@email.com\" \"password\""
    exit 1
fi

echo "📋 Creando nuevo tenant: $TENANT_NAME"

docker compose exec app php artisan tinker << EOF
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;

// Crear tenant
\$tenant = Tenant::create([
    'ruc' => '80000000-' . (Tenant::count() + 1),
    'name' => '$TENANT_NAME',
    'email' => '$ADMIN_EMAIL',
    'phone' => '+595 21 000 000',
    'address' => 'Dirección',
    'city' => 'Asunción',
    'country' => 'Paraguay',
    'is_active' => true,
]);

echo "✅ Tenant creado con ID: " . \$tenant->id . "\n";

// Crear usuario admin para este tenant
\$adminRole = Role::where('slug', 'admin')->first();

\$user = User::create([
    'tenant_id' => \$tenant->id,
    'name' => 'Administrador',
    'email' => '$ADMIN_EMAIL',
    'password' => Hash::make('$ADMIN_PASSWORD'),
    'is_active' => true,
    'email_verified_at' => now(),
]);

\$user->roles()->sync([\$adminRole->id]);

echo "✅ Usuario admin creado: $ADMIN_EMAIL\n";
echo "🔑 Contraseña: $ADMIN_PASSWORD\n";
echo "\n";
echo "════════════════════════════════════════\n";
echo "✅ Tenant configurado exitosamente\n";
echo "════════════════════════════════════════\n";
echo "🏢 Empresa: $TENANT_NAME\n";
echo "📧 Email: $ADMIN_EMAIL\n";
echo "🔑 Password: $ADMIN_PASSWORD\n";
echo "🌐 URL: https://demo-erp.neosystem.com.py\n";
echo "\n";
echo "⚠️  IMPORTANTE: El cliente debe cambiar su contraseña al primer login\n";

exit
EOF

echo ""
echo "✅ Proceso completado!"
