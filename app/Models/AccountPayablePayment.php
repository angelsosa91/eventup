<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayablePayment extends Model
{
    protected $fillable = [
        'account_payable_id',
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

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public static function generatePaymentNumber($accountPayableId)
    {
        $last = self::where('account_payable_id', $accountPayableId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->payment_number, 5)) + 1;
        } else {
            $number = 1;
        }

        return 'PAGO-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
