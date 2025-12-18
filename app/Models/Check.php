<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Check extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'check_number',
        'issue_date',
        'due_date',
        'amount',
        'type',
        'bank_account_id',
        'payee',
        'bank_name',
        'issuer',
        'concept',
        'notes',
        'bank_transaction_id',
        'account_receivable_payment_id',
        'account_payable_payment_id',
        'status',
        'cashed_date',
        'user_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'cashed_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }

    public function accountReceivablePayment(): BelongsTo
    {
        return $this->belongsTo(AccountReceivablePayment::class);
    }

    public function accountPayablePayment(): BelongsTo
    {
        return $this->belongsTo(AccountPayablePayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el cheque está vencido
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date < now() && in_array($this->status, ['pending', 'deposited']);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($check) {
            // Si un cheque emitido es cobrado, actualizar el saldo de la cuenta
            if ($check->type === 'issued' && $check->isDirty('status') && $check->status === 'cashed') {
                $check->bankAccount->updateBalance();
            }
        });
    }
}
