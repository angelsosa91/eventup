<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'tax_rate',
        'subtotal',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la venta
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcular valores del item
     * En Paraguay el IVA está incluido en el precio
     */
    public function calculateValues(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;

        // Calcular IVA (incluido en el precio)
        if ($this->tax_rate > 0) {
            // Fórmula: IVA = Subtotal * tasa / (100 + tasa)
            $this->tax_amount = round($this->subtotal * $this->tax_rate / (100 + $this->tax_rate), 2);
        } else {
            $this->tax_amount = 0;
        }

        $this->total = $this->subtotal;
    }

    /**
     * Obtener el subtotal sin IVA
     */
    public function getSubtotalSinIvaAttribute(): float
    {
        return $this->subtotal - $this->tax_amount;
    }
}
