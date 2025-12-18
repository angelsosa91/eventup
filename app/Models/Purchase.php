<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'purchase_number',
        'purchase_date',
        'supplier_id',
        'user_id',
        'invoice_number',
        'subtotal_exento',
        'subtotal_5',
        'iva_5',
        'subtotal_10',
        'iva_10',
        'total',
        'status',
        'journal_entry_id',
        'payment_method',
        'payment_type',
        'credit_days',
        'credit_due_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'credit_due_date' => 'date',
        'subtotal_exento' => 'decimal:2',
        'subtotal_5' => 'decimal:2',
        'iva_5' => 'decimal:2',
        'subtotal_10' => 'decimal:2',
        'iva_10' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function accountPayable()
    {
        return $this->hasOne(AccountPayable::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Generar número de compra automático
     */
    public static function generatePurchaseNumber($tenantId)
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->purchase_number, 2)) + 1;
        } else {
            $number = 1;
        }

        return 'C-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular totales basados en los items
     */
    public function calculateTotals()
    {
        $totals = [
            'exento' => 0,
            'gravado_5' => 0,
            'iva_5' => 0,
            'gravado_10' => 0,
            'iva_10' => 0,
        ];

        foreach ($this->items as $item) {
            switch ($item->tax_rate) {
                case 0:
                    $totals['exento'] += $item->subtotal;
                    break;
                case 5:
                    $totals['gravado_5'] += $item->subtotal;
                    $totals['iva_5'] += $item->iva;
                    break;
                case 10:
                    $totals['gravado_10'] += $item->subtotal;
                    $totals['iva_10'] += $item->iva;
                    break;
            }
        }

        $this->subtotal_exento = $totals['exento'];
        $this->subtotal_5 = $totals['gravado_5'];
        $this->iva_5 = $totals['iva_5'];
        $this->subtotal_10 = $totals['gravado_10'];
        $this->iva_10 = $totals['iva_10'];
        $this->total = $totals['exento'] + $totals['gravado_5'] + $totals['gravado_10'];

        return $this;
    }

    /**
     * Obtener el total de IVA
     */
    public function getTotalIvaAttribute(): float
    {
        return $this->iva_5 + $this->iva_10;
    }
}
