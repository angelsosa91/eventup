<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'key',
        'account_id',
        'description',
    ];

    /**
     * Relación con la cuenta contable
     */
    public function account()
    {
        return $this->belongsTo(AccountChart::class, 'account_id');
    }

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener el valor de una configuración
     */
    public static function getValue($tenantId, $key)
    {
        $setting = self::where('tenant_id', $tenantId)
            ->where('key', $key)
            ->first();

        return $setting ? $setting->account_id : null;
    }

    /**
     * Establecer el valor de una configuración
     */
    public static function setValue($tenantId, $key, $accountId, $description = null)
    {
        return self::updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['account_id' => $accountId, 'description' => $description]
        );
    }

    /**
     * Claves de configuración disponibles
     */
    public static function getAvailableKeys()
    {
        return [
            // Ventas
            'sales_income' => 'Cuenta de Ingresos por Ventas',
            'sales_tax' => 'Cuenta de IVA Ventas',
            'sales_discount' => 'Cuenta de Descuentos en Ventas',

            // Compras
            'purchases_expense' => 'Cuenta de Compras / Costo de Ventas',
            'purchases_tax' => 'Cuenta de IVA Compras',
            'purchases_discount' => 'Cuenta de Descuentos en Compras',

            // Cuentas por Cobrar y Pagar
            'accounts_receivable' => 'Cuenta de Cuentas por Cobrar',
            'accounts_payable' => 'Cuenta de Cuentas por Pagar',

            // Caja y Bancos
            'cash' => 'Cuenta de Caja',
            'bank_default' => 'Cuenta de Banco por Defecto',

            // Inventario
            'inventory' => 'Cuenta de Inventario',

            // Gastos
            'expenses_default' => 'Cuenta de Gastos por Defecto',
        ];
    }
}
