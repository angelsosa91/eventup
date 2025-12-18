<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'bank_id',
        'account_number',
        'account_name',
        'bank_name',
        'bank_code',
        'account_type',
        'currency',
        'initial_balance',
        'current_balance',
        'account_holder',
        'swift_code',
        'notes',
        'status',
        'is_default',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(BankReconciliation::class);
    }

    /**
     * Actualizar el saldo de la cuenta
     */
    public function updateBalance(): void
    {
        $deposits = $this->transactions()
            ->whereIn('type', ['deposit', 'transfer_in', 'interest'])
            ->where('status', 'completed')
            ->sum('amount');

        $withdrawals = $this->transactions()
            ->whereIn('type', ['withdrawal', 'transfer_out', 'check', 'charge'])
            ->where('status', 'completed')
            ->sum('amount');

        $this->current_balance = $this->initial_balance + $deposits - $withdrawals;
        $this->save();
    }

    /**
     * Obtener saldo disponible (incluyendo cheques pendientes)
     */
    public function getAvailableBalanceAttribute(): float
    {
        $pendingChecks = $this->checks()
            ->where('type', 'issued')
            ->whereIn('status', ['pending', 'deposited'])
            ->sum('amount');

        return $this->current_balance - $pendingChecks;
    }

    /**
     * Obtener transacciones no conciliadas
     */
    public function getUnreconciledTransactions()
    {
        return $this->transactions()
            ->where('reconciled', false)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Obtener la cuenta bancaria predeterminada
     */
    public static function getDefaultAccount($tenantId)
    {
        return self::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Establecer esta cuenta como predeterminada
     */
    public function setAsDefault(): void
    {
        // Quitar el flag is_default de todas las cuentas del tenant
        self::where('tenant_id', $this->tenant_id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Establecer esta cuenta como predeterminada
        $this->is_default = true;
        $this->save();
    }

    /**
     * Scope para cuentas activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
