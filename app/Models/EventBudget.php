<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventBudget extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'customer_id',
        'family_name',
        'budget_date',
        'total_amount',
        'status',
        'notes',
        'table_id',
    ];

    protected $casts = [
        'budget_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(EventTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventBudgetItem::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(EventBudgetGuest::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'sent' => 'Enviado',
            'accepted' => 'Aceptado',
            'rejected' => 'Rechazado',
            default => $this->status,
        };
    }

    public function updateTotal(): void
    {
        $this->total_amount = $this->items()->sum('total');
        $this->save();
    }
}
