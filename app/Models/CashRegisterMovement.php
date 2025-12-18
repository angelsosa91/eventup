<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterMovement extends Model
{
    protected $fillable = [
        'cash_register_id',
        'type',
        'concept',
        'description',
        'amount',
        'reference',
        'sale_id',
        'purchase_id',
        'account_receivable_payment_id',
        'account_payable_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function accountReceivablePayment()
    {
        return $this->belongsTo(AccountReceivablePayment::class);
    }

    public function accountPayablePayment()
    {
        return $this->belongsTo(AccountPayablePayment::class);
    }
}
