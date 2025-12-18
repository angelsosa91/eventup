<?php

namespace Database\Seeders;

use App\Models\AccountChart;
use App\Models\AccountingSetting;
use Illuminate\Database\Seeder;

class AccountingSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder configura las cuentas contables por defecto para el tenant 1
     */
    public function run(): void
    {
        $tenantId = 1;

        // Obtener las cuentas necesarias por su código
        $accounts = [
            'sales_income' => '4.1',           // Ventas
            'sales_tax' => '2.1.02',          // IVA Débito Fiscal
            'purchases_expense' => '5.1',      // Costo de Ventas
            'purchases_tax' => '1.1.05',      // IVA Crédito Fiscal
            'accounts_receivable' => '1.1.03', // Cuentas por Cobrar
            'accounts_payable' => '2.1.01',   // Cuentas por Pagar
            'cash' => '1.1.01',               // Caja
            'bank_default' => '1.1.02',       // Bancos
            'inventory' => '1.1.04',          // Inventario
            'expenses_default' => '5.2',      // Gastos de Administración
        ];

        foreach ($accounts as $key => $code) {
            $account = AccountChart::where('tenant_id', $tenantId)
                ->where('code', $code)
                ->first();

            if ($account) {
                AccountingSetting::setValue(
                    $tenantId,
                    $key,
                    $account->id,
                    AccountingSetting::getAvailableKeys()[$key]
                );
            }
        }
    }
}
