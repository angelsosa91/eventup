<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'user_id',
        'contribution_number',
        'contribution_date',
        'amount',
        'payment_method',
        'reference',
        'status',
        'journal_entry_id',
        'refunded_from_id',
        'notes',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relación con el alumno (Customer)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con el usuario que creó el aporte
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el asiento contable
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Relación con el aporte que se está devolviendo (si es una devolución)
     */
    public function refundedFrom(): BelongsTo
    {
        return $this->belongsTo(Contribution::class, 'refunded_from_id');
    }

    /**
     * Verificar si este aporte ya fue devuelto
     */
    public function hasBeenRefunded(): bool
    {
        return self::where('refunded_from_id', $this->id)
            ->where('status', 'confirmed')
            ->exists();
    }

    /**
     * Verificar si es una devolución (monto negativo)
     */
    public function isRefund(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Generar número de aporte automático
     */
    public static function generateContributionNumber(int $tenantId): string
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNumber = intval(substr($last->contribution_number, -7));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'AP-' . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si el aporte puede ser anulado
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== 'cancelled';
    }

    /**
     * Obtener etiqueta de estado
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'confirmed' => $this->amount < 0 ? 'Devuelto' : 'Confirmado',
            'cancelled' => 'Anulado',
            default => $this->status,
        };
    }
}
