<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'adjustment_number',
        'adjustment_date',
        'user_id',
        'type',
        'reason',
        'notes',
        'status',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public static function generateNumber($tenantId)
    {
        $last = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $number = intval(substr($last->adjustment_number, 3)) + 1;
        } else {
            $number = 1;
        }

        return 'AJ-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }
}
