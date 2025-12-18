<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationLine extends Model
{
    protected $fillable = [
        'bank_reconciliation_id',
        'bank_transaction_id',
        'matched_in_statement',
        'notes',
    ];

    protected $casts = [
        'matched_in_statement' => 'boolean',
    ];

    public function bankReconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }
}
