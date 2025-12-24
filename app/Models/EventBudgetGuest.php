<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBudgetGuest extends Model
{
    protected $fillable = [
        'event_budget_id',
        'name',
        'cedula',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(EventBudget::class, 'event_budget_id');
    }
}
