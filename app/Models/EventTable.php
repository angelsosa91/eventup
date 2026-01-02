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
        // La capacidad se basa en los invitados de los presupuestos (familias) asignados
        $familyGuests = $this->budgets->sum(function ($b) {
            return $b->guests()->count();
        });

        // Sumamos los invitados globales del presupuesto (items marcados como cuenta invitados)
        // Nota: Estos invitados se consideran "base" para el evento. 
        // Si el usuario quiere que afecten a CADA mesa, se suman aquí.
        // Si son "extra" para el total del evento, la lógica podría variar.
        // Según el requerimiento: "debemos tomar en cuenta la suma de los valores de la cantidad de los items... que tengan la relacion como 'Cuenta Invitado'"
        $itemGuests = $this->event->item_guests_count;

        return $familyGuests + $itemGuests;
    }

    public function getColorAttribute(): string
    {
        $totalGuests = $this->capacity; // Usa el accessor de capacidad

        return match (true) {
            $totalGuests >= 1 && $totalGuests <= 4 => '#ade8f4', // Azul claro
            $totalGuests >= 5 && $totalGuests <= 7 => '#ffff00', // Amarillo
            $totalGuests >= 8 && $totalGuests <= 10 => '#d8bfd8', // Lila
            $totalGuests >= 11 && $totalGuests <= 14 => '#b7e4c7', // Verde claro
            $totalGuests >= 15 => '#ff9e00', // Naranja
            default => '#e0e0e0', // Gris por defecto
        };
    }
}
