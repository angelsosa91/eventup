<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBudgetItem extends Model
{
    protected $fillable = [
        'event_budget_id',
        'description',
        'quantity',
        'unit_price',
        'total',
        'notes',
        'count_guests',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'count_guests' => 'boolean',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(EventBudget::class, 'event_budget_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            $item->budget->updateTotal();
        });

        static::deleted(function ($item) {
            $item->budget->updateTotal();
        });
    }
}
