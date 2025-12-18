<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'document_number',
        'document_date',
        'due_date',
        'customer_id',
        'customer_name',
        'sale_id',
        'sale_number',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class);
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

        return 'CC-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    public static function createFromSale(Sale $sale)
    {
        return self::create([
            'tenant_id' => $sale->tenant_id,
            'document_number' => self::generateDocumentNumber($sale->tenant_id),
            'document_date' => $sale->sale_date,
            'due_date' => $sale->sale_date->addDays(30),
            'customer_id' => $sale->customer_id,
            'customer_name' => $sale->customer_name,
            'sale_id' => $sale->id,
            'sale_number' => $sale->sale_number,
            'description' => 'Venta a crédito - ' . $sale->sale_number,
            'amount' => $sale->total,
            'paid_amount' => 0,
            'balance' => $sale->total,
            'status' => 'pending',
        ]);
    }
}
