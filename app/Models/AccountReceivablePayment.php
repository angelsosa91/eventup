<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReceivablePayment extends Model
{
    protected $fillable = [
        'account_receivable_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'user_id',
        'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public static function generatePaymentNumber($accountReceivableId)
    {
        $last = self::where('account_receivable_id', $accountReceivableId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->payment_number, 5)) + 1;
        } else {
            $number = 1;
        }

        return 'RCBO-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
