<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CompanySetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'company_name',
        'ruc',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'slogan',
        'currency',
        'currency_symbol',
        'decimal_places',
        'date_format',
        'timezone',
        'invoice_requires_tax_id',
        'low_stock_threshold',
    ];

    protected $casts = [
        'invoice_requires_tax_id' => 'boolean',
        'decimal_places' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Obtener configuración de la empresa del tenant actual
     */
    public static function getCurrentSettings()
    {
        $tenantId = Auth::user()->tenant_id;
        return self::where('tenant_id', $tenantId)->first();
    }

    /**
     * Obtener o crear configuración con valores por defecto
     */
    public static function getOrCreate($tenantId)
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'company_name' => 'Mi Empresa',
                'currency' => 'PYG',
                'currency_symbol' => 'Gs.',
                'decimal_places' => 0,
                'date_format' => 'd/m/Y',
                'timezone' => 'America/Asuncion',
                'low_stock_threshold' => 10,
                'invoice_requires_tax_id' => false,
            ]
        );
    }
}
