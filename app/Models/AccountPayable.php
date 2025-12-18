<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'document_number',
        'document_date',
        'due_date',
        'supplier_id',
        'supplier_name',
        'purchase_id',
        'purchase_number',
        'description',
        'amount',
        'paid_amount',
        'balance',
        'status',
    ];

    protected $casts = [
        'document_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountPayablePayment::class);
    }

    public function updateBalance()
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->balance = $this->amount - $this->paid_amount;

        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }

    public static function generateDocumentNumber($tenantId)
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->document_number, 3)) + 1;
        } else {
            $number = 1;
        }

        return 'CP-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }
}
