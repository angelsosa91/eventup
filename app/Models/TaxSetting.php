<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TaxSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'rate',
        'code',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener impuestos activos del tenant
     */
    public static function getActiveTaxes($tenantId = null)
    {
        if (!$tenantId) {
            $tenantId = Auth::user()->tenant_id;
        }

        return self::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('rate', 'desc')
            ->get();
    }

    /**
     * Obtener impuesto por defecto
     */
    public static function getDefaultTax($tenantId = null)
    {
        if (!$tenantId) {
            $tenantId = Auth::user()->tenant_id;
        }

        return self::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Inicializar impuestos por defecto para Paraguay
     */
    public static function initializeDefaults($tenantId)
    {
        $defaults = [
            ['name' => 'IVA 10%', 'rate' => 10, 'code' => 'IVA10', 'is_default' => true],
            ['name' => 'IVA 5%', 'rate' => 5, 'code' => 'IVA5', 'is_default' => false],
            ['name' => 'Exento', 'rate' => 0, 'code' => 'EXE', 'is_default' => false],
        ];

        foreach ($defaults as $tax) {
            self::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'code' => $tax['code'],
                ],
                [
                    'name' => $tax['name'],
                    'rate' => $tax['rate'],
                    'is_default' => $tax['is_default'],
                    'is_active' => true,
                ]
            );
        }
    }
}
