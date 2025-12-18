<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class BankTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'bank_account_id',
        'transaction_number',
        'transaction_date',
        'type',
        'amount',
        'reference',
        'concept',
        'description',
        'cash_register_id',
        'account_receivable_payment_id',
        'account_payable_payment_id',
        'related_transaction_id',
        'destination_account_id',
        'balance_after',
        'user_id',
        'status',
        'reconciled',
        'reconciled_date',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'reconciled' => 'boolean',
        'reconciled_date' => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'destination_account_id');
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class, 'related_transaction_id');
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
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
     * Generar número de transacción
     */
    public static function generateTransactionNumber($tenantId): string
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->transaction_number, 3)) + 1;
        } else {
            $number = 1;
        }

        return 'BT-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            // Actualizar saldo de la cuenta después de crear la transacción
            if ($transaction->status === 'completed') {
                $transaction->bankAccount->updateBalance();
            }
        });

        static::updated(function ($transaction) {
            // Actualizar saldo si el estado cambió
            if ($transaction->isDirty('status') && $transaction->status === 'completed') {
                $transaction->bankAccount->updateBalance();
            }
        });
    }
}
