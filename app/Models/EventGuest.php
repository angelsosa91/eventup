<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGuest extends Model
{
    protected $fillable = [
        'event_id',
        'table_id',
        'name',
        'email',
        'phone',
        'is_validated',
    ];

    protected $casts = [
        'is_validated' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(EventTable::class, 'table_id');
    }
}
