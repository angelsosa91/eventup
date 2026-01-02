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
        'color',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(EventGuest::class, 'table_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(EventBudget::class, 'table_id');
    }

    public function getCapacityAttribute()
    {
        // La capacidad se basa en los invitados de los items marcados en los presupuestos (familias) asignados
        return $this->budgets->sum(function ($b) {
            return $b->item_guests_count;
        });
    }

    public function getColorAttribute(): string
    {
        $totalGuests = $this->capacity; // Usa el accessor de capacidad

        return match (true) {
            $totalGuests >= 1 && $totalGuests <= 7 => '#ffff00',   // Amarillo
            $totalGuests >= 8 && $totalGuests <= 10 => '#d8bfd8',  // Lila
            $totalGuests >= 11 && $totalGuests <= 12 => '#90ee90', // Verde
            $totalGuests >= 13 && $totalGuests <= 14 => '#ffc0cb', // Rosado
            $totalGuests >= 15 && $totalGuests <= 16 => '#8b4513', // Marron
            $totalGuests >= 17 && $totalGuests <= 20 => '#87ceeb', // Celeste
            $totalGuests >= 21 => '#ff0000',                       // Rojo
            default => '#e0e0e0',                                 // Gris por defecto
        };
    }
}
