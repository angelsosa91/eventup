<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'register_number',
        'register_date',
        'user_id',
        'opening_balance',
        'sales_cash',
        'collections',
        'payments',
        'expenses',
        'expected_balance',
        'actual_balance',
        'difference',
        'status',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'register_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'sales_cash' => 'decimal:2',
        'collections' => 'decimal:2',
        'payments' => 'decimal:2',
        'expenses' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->hasMany(CashRegisterMovement::class);
    }

    public function calculateExpectedBalance()
    {
        $this->expected_balance = $this->opening_balance
            + $this->sales_cash
            + $this->collections
            - $this->payments
            - $this->expenses;

        return $this->expected_balance;
    }

    public function calculateDifference()
    {
        if ($this->actual_balance !== null) {
            $this->difference = $this->actual_balance - $this->expected_balance;
        }
        return $this->difference;
    }

    public static function generateRegisterNumber($tenantId)
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->register_number, 3)) + 1;
        } else {
            $number = 1;
        }

        return 'CJ-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    public static function getOpenRegister($tenantId, $userId)
    {
        return self::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->where('register_date', date('Y-m-d'))
            ->first();
    }

    public static function getUserRegisterForDate($tenantId, $userId, $date)
    {
        return self::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('register_date', $date)
            ->first();
    }
}
