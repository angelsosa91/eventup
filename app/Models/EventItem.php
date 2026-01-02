<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventItem extends Model
{
    protected $table = 'event_items';

    protected $fillable = [
        'event_id',
        'product_id',
        'description',
        'quantity',
        'estimated_unit_price',
        'total',
        'notes',
        'count_guests',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'count_guests' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Boot method to calculate total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->quantity * $item->estimated_unit_price;
        });
    }
}
