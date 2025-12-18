<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remission extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'remission_number',
        'customer_id',
        'date',
        'delivery_address',
        'reason',
        'status',
        'sale_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con el cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con los items de la remisión
     */
    public function items()
    {
        return $this->hasMany(RemissionItem::class);
    }

    /**
     * Relación con la venta (si fue convertida)
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el usuario que creó la remisión
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generar el siguiente número de remisión
     */
    public static function generateRemissionNumber()
    {
        $lastRemission = static::orderBy('id', 'desc')->first();

        if (!$lastRemission) {
            return 'R-0000001';
        }

        $lastNumber = intval(substr($lastRemission->remission_number, 2));
        $newNumber = $lastNumber + 1;

        return 'R-' . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Scope para remisiones confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope para remisiones en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope para remisiones pendientes de facturar
     */
    public function scopePendingInvoice($query)
    {
        return $query->whereIn('status', ['confirmed', 'delivered']);
    }

    /**
     * Obtener el texto del motivo
     */
    public function getReasonTextAttribute()
    {
        $reasons = [
            'transfer' => 'Traslado entre sucursales',
            'consignment' => 'Consignación',
            'demo' => 'Demostración',
            'delivery' => 'Entrega',
        ];

        return $reasons[$this->reason] ?? $this->reason;
    }

    /**
     * Obtener el texto del estado
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'draft' => 'Borrador',
            'confirmed' => 'Confirmada',
            'delivered' => 'Entregada',
            'invoiced' => 'Facturada',
            'cancelled' => 'Anulada',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Obtener badge del estado
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-secondary',
            'confirmed' => 'bg-primary',
            'delivered' => 'bg-info',
            'invoiced' => 'bg-success',
            'cancelled' => 'bg-danger',
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    /**
     * Verificar si puede ser convertida a factura
     */
    public function canBeConvertedToInvoice()
    {
        return in_array($this->status, ['confirmed', 'delivered']) && !$this->sale_id;
    }

    /**
     * Verificar si puede ser anulada
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'confirmed', 'delivered']);
    }

    /**
     * Verificar si puede ser confirmada
     */
    public function canBeConfirmed()
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si puede ser marcada como entregada
     */
    public function canBeDelivered()
    {
        return $this->status === 'confirmed';
    }
}
