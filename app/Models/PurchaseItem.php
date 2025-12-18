<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'tax_rate',
        'subtotal',
        'iva',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcular subtotal e IVA
     * El IVA está incluido en el precio
     */
    public function calculateValues()
    {
        $this->subtotal = $this->quantity * $this->unit_price;

        if ($this->tax_rate > 0) {
            // IVA incluido: subtotal * tasa / (100 + tasa)
            $this->iva = $this->subtotal * $this->tax_rate / (100 + $this->tax_rate);
        } else {
            $this->iva = 0;
        }

        return $this;
    }
}
