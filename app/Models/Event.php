<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'event_date',
        'estimated_budget',
        'status',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'estimated_budget' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(EventItem::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(EventTable::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(EventGuest::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(EventBudget::class);
    }

    /**
     * Obtener el conteo total de invitados provenientes de items del presupuesto
     * que tienen marcado 'count_guests'
     */
    public function getItemGuestsCountAttribute(): int
    {
        return (int) $this->items()->where('count_guests', true)->sum('quantity');
    }

    /**
     * Obtener etiqueta de estado
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Anulado',
            'completed' => 'Completado',
            default => $this->status,
        };
    }
}
