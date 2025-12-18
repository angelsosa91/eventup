<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Asiento contable al que pertenece
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Cuenta contable
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountChart::class, 'account_id');
    }
}
