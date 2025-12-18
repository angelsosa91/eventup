<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'document_type',
        'prefix',
        'series',
        'next_number',
        'padding',
        'format',
        'is_active',
    ];

    protected $casts = [
        'next_number' => 'integer',
        'padding' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Generar el siguiente número de documento
     */
    public function generateDocumentNumber()
    {
        $number = str_pad($this->next_number, $this->padding, '0', STR_PAD_LEFT);

        // Incrementar el número para el próximo uso
        $this->increment('next_number');

        // Aplicar el formato
        return $this->applyFormat($number);
    }

    /**
     * Aplicar formato al número
     */
    private function applyFormat($number)
    {
        $parts = [];

        if ($this->prefix) {
            $parts[] = $this->prefix;
        }

        if ($this->series) {
            $parts[] = $this->series;
        }

        $parts[] = $number;

        return implode('-', $parts);
    }

    /**
     * Obtener configuración activa para un tipo de documento
     */
    public static function getActiveForType($tenantId, $documentType, $series = null)
    {
        $query = self::where('tenant_id', $tenantId)
            ->where('document_type', $documentType)
            ->where('is_active', true);

        if ($series) {
            $query->where('series', $series);
        }

        return $query->first();
    }

    /**
     * Obtener configuraciones disponibles por tipo
     */
    public static function getAvailableDocumentTypes()
    {
        return [
            'sale' => 'Ventas / Facturas',
            'purchase' => 'Compras',
            'expense' => 'Gastos',
            'adjustment' => 'Ajustes de Inventario',
            'payment_receipt' => 'Recibos de Pago',
            'payment_voucher' => 'Comprobantes de Pago',
            'journal_entry' => 'Asientos Contables',
        ];
    }
}
