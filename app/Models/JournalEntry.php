<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class JournalEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'entry_number',
        'entry_date',
        'period',
        'entry_type',
        'status',
        'description',
        'notes',
        'reference_type',
        'reference_id',
        'total_debit',
        'total_credit',
        'is_balanced',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'is_balanced' => 'boolean',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    /**
     * Usuario que creó el asiento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Líneas del asiento contable
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Scope para asientos contabilizados
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope para asientos en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope por período
     */
    public function scopeOfPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Generar el siguiente número de asiento
     */
    public static function generateEntryNumber(int $tenantId, string $period): string
    {
        $lastEntry = self::where('tenant_id', $tenantId)
            ->where('period', $period)
            ->orderBy('entry_number', 'desc')
            ->first();

        if (!$lastEntry) {
            return $period . '-0001';
        }

        $parts = explode('-', $lastEntry->entry_number);
        $nextNumber = str_pad((int)end($parts) + 1, 4, '0', STR_PAD_LEFT);

        return $period . '-' . $nextNumber;
    }

    /**
     * Calcular totales del asiento
     */
    public function calculateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit');
        $this->total_credit = $this->lines()->sum('credit');
        $this->is_balanced = abs($this->total_debit - $this->total_credit) < 0.01;
        $this->save();
    }

    /**
     * Actualizar saldos de las cuentas afectadas por este asiento
     */
    public function updateAccountBalances(): void
    {
        foreach ($this->lines as $line) {
            $account = $line->account;
            if ($account) {
                $account->updateBalance();
            }
        }
    }

    /**
     * Contabilizar el asiento (cambiar estado a posted)
     */
    public function post(): bool
    {
        if ($this->status === 'posted') {
            throw new \Exception('El asiento ya está contabilizado');
        }

        if (!$this->is_balanced) {
            throw new \Exception('El asiento no está balanceado. Débitos: ' . $this->total_debit . ', Créditos: ' . $this->total_credit);
        }

        DB::beginTransaction();
        try {
            $this->status = 'posted';
            $this->posted_at = now();
            $this->save();

            // Actualizar saldos de las cuentas
            foreach ($this->lines as $line) {
                $line->account->updateBalance();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Anular el asiento
     */
    public function cancel(): bool
    {
        if ($this->status === 'cancelled') {
            throw new \Exception('El asiento ya está anulado');
        }

        DB::beginTransaction();
        try {
            $this->status = 'cancelled';
            $this->save();

            // Si estaba contabilizado, actualizar saldos
            if ($this->status === 'posted') {
                foreach ($this->lines as $line) {
                    $line->account->updateBalance();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
