<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'code',
        'name',
        'description',
        'barcode',
        'unit',
        'purchase_price',
        'sale_price',
        'tax_rate',
        'stock',
        'min_stock',
        'max_stock',
        'track_stock',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'integer',
        'stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
