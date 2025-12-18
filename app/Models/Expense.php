<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'expense_number',
        'expense_date',
        'expense_category_id',
        'supplier_id',
        'user_id',
        'journal_entry_id',
        'document_number',
        'description',
        'amount',
        'tax_rate',
        'tax_amount',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Generar número de gasto automático
     */
    public static function generateExpenseNumber($tenantId)
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->expense_number, 2)) + 1;
        } else {
            $number = 1;
        }

        return 'G-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    /**
     * Calcular IVA basado en el monto
     */
    public function calculateTax()
    {
        if ($this->tax_rate > 0) {
            // IVA incluido: amount * tasa / (100 + tasa)
            $this->tax_amount = $this->amount * $this->tax_rate / (100 + $this->tax_rate);
        } else {
            $this->tax_amount = 0;
        }

        return $this;
    }
}
