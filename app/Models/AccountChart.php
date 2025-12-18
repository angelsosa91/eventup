<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountChart extends Model
{
    use BelongsToTenant;

    protected $table = 'account_chart';

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'code',
        'name',
        'description',
        'account_type',
        'nature',
        'level',
        'is_detail',
        'is_active',
        'opening_balance',
        'current_balance',
    ];

    protected $casts = [
        'is_detail' => 'boolean',
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'level' => 'integer',
    ];

    /**
     * Cuenta padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountChart::class, 'parent_id');
    }

    /**
     * Cuentas hijas
     */
    public function children(): HasMany
    {
        return $this->hasMany(AccountChart::class, 'parent_id');
    }

    /**
     * Líneas de asientos contables
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /**
     * Scope para cuentas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para cuentas de detalle
     */
    public function scopeDetail($query)
    {
        return $query->where('is_detail', true);
    }

    /**
     * Scope por tipo de cuenta
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Obtener el código completo de la cuenta
     */
    public function getFullCodeAttribute(): string
    {
        if ($this->parent_id) {
            return $this->parent->full_code . '.' . $this->code;
        }
        return $this->code;
    }

    /**
     * Actualizar saldo de la cuenta
     */
    public function updateBalance(): void
    {
        $debits = $this->journalEntryLines()
            ->whereHas('journalEntry', function($q) {
                $q->where('status', 'posted');
            })
            ->sum('debit');

        $credits = $this->journalEntryLines()
            ->whereHas('journalEntry', function($q) {
                $q->where('status', 'posted');
            })
            ->sum('credit');

        if ($this->nature === 'debit') {
            $this->current_balance = $this->opening_balance + $debits - $credits;
        } else {
            $this->current_balance = $this->opening_balance + $credits - $debits;
        }

        $this->save();
    }

    /**
     * Generar el siguiente código para una cuenta hija
     */
    public static function generateNextCode(?int $parentId, int $tenantId): string
    {
        if (!$parentId) {
            // Cuenta de nivel 1
            $lastAccount = self::where('tenant_id', $tenantId)
                ->whereNull('parent_id')
                ->orderBy('code', 'desc')
                ->first();

            return $lastAccount ? (string)((int)$lastAccount->code + 1) : '1';
        }

        // Cuenta hija
        $parent = self::findOrFail($parentId);
        $lastChild = $parent->children()->orderBy('code', 'desc')->first();

        if (!$lastChild) {
            return $parent->code . '.01';
        }

        $lastCode = explode('.', $lastChild->code);
        $lastNumber = (int)end($lastCode);
        $nextNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

        $parentCode = implode('.', array_slice($lastCode, 0, -1));
        return $parentCode ? $parentCode . '.' . $nextNumber : $nextNumber;
    }
}
