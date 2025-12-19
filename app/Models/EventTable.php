<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTable extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'capacity',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(EventGuest::class, 'table_id');
    }
}
