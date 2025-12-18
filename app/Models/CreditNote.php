<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'credit_note_number',
        'sale_id',
        'customer_id',
        'date',
        'reason',
        'type',
        'subtotal_0',
        'subtotal_5',
        'subtotal_10',
        'iva_5',
        'iva_10',
        'total',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'subtotal_0' => 'decimal:2',
        'subtotal_5' => 'decimal:2',
        'subtotal_10' => 'decimal:2',
        'iva_5' => 'decimal:2',
        'iva_10' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con el tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relación con la venta original
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el cliente
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con los items de la nota de crédito
     */
    public function items()
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    /**
     * Relación con el usuario que creó la nota de crédito
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el asiento contable
     */
    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'source');
    }

    /**
     * Generar el siguiente número de nota de crédito
     */
    public static function generateCreditNoteNumber()
    {
        $lastCreditNote = static::orderBy('id', 'desc')->first();

        if (!$lastCreditNote) {
            return 'NC-0000001';
        }

        $lastNumber = intval(substr($lastCreditNote->credit_note_number, 3));
        $newNumber = $lastNumber + 1;

        return 'NC-' . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Scope para notas de crédito confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope para notas de crédito en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Obtener el texto del motivo
     */
    public function getReasonTextAttribute()
    {
        $reasons = [
            'return' => 'Devolución de mercadería',
            'discount' => 'Descuento',
            'error' => 'Error en facturación',
            'cancellation' => 'Anulación',
        ];

        return $reasons[$this->reason] ?? $this->reason;
    }

    /**
     * Obtener el texto del tipo
     */
    public function getTypeTextAttribute()
    {
        $types = [
            'total' => 'Total',
            'partial' => 'Parcial',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Obtener el texto del estado
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'draft' => 'Borrador',
            'confirmed' => 'Confirmada',
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
            'confirmed' => 'bg-success',
            'cancelled' => 'bg-danger',
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }
}
