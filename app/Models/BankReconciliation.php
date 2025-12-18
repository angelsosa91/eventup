<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'bank_account_id',
        'reconciliation_number',
        'reconciliation_date',
        'statement_start_date',
        'statement_end_date',
        'opening_balance',
        'closing_balance',
        'system_balance',
        'difference',
        'status',
        'notes',
        'reconciled_by',
        'posted_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'statement_start_date' => 'date',
        'statement_end_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'posted_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankReconciliationLine::class);
    }

    /**
     * Generar el siguiente número de conciliación
     */
    public static function generateReconciliationNumber($tenantId): string
    {
        $lastReconciliation = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastReconciliation) {
            return 'CON-' . date('Y') . '-0001';
        }

        $lastNumber = (int) substr($lastReconciliation->reconciliation_number, -4);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return 'CON-' . date('Y') . '-' . $newNumber;
    }

    /**
     * Publicar la conciliación
     */
    public function post(): void
    {
        if ($this->status !== 'draft') {
            throw new \Exception('Solo se pueden publicar conciliaciones en borrador');
        }

        \DB::beginTransaction();
        try {
            // Marcar todas las transacciones incluidas como conciliadas
            foreach ($this->lines as $line) {
                $transaction = $line->bankTransaction;
                $transaction->reconciled = true;
                $transaction->reconciled_date = $this->reconciliation_date;
                $transaction->save();
            }

            // Actualizar el estado de la conciliación
            $this->status = 'posted';
            $this->posted_at = now();
            $this->reconciled_by = auth()->id();
            $this->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancelar la conciliación
     */
    public function cancel(): void
    {
        if ($this->status !== 'posted') {
            throw new \Exception('Solo se pueden cancelar conciliaciones publicadas');
        }

        \DB::beginTransaction();
        try {
            // Desmarcar todas las transacciones incluidas
            foreach ($this->lines as $line) {
                $transaction = $line->bankTransaction;
                $transaction->reconciled = false;
                $transaction->reconciled_date = null;
                $transaction->save();
            }

            // Actualizar el estado de la conciliación
            $this->status = 'cancelled';
            $this->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcular la diferencia
     */
    public function calculateDifference(): void
    {
        $this->difference = $this->closing_balance - $this->system_balance;
        $this->save();
    }

    /**
     * Verificar si está balanceada
     */
    public function isBalanced(): bool
    {
        return abs($this->difference) < 0.01; // Tolerancia de 1 centavo
    }

    /**
     * Scope para conciliaciones en borrador
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope para conciliaciones publicadas
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Scope para conciliaciones canceladas
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
