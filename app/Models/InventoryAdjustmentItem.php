<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'inventory_adjustment_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
