<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemissionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'remission_id',
        'product_id',
        'quantity',
        'reserved_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
    ];

    /**
     * Relación con la remisión
     */
    public function remission()
    {
        return $this->belongsTo(Remission::class);
    }

    /**
     * Relación con el producto
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
