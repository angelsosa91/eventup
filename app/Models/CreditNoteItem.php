<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_note_id',
        'product_id',
        'quantity',
        'price',
        'iva_type',
        'subtotal',
        'iva_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la nota de crédito
     */
    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
