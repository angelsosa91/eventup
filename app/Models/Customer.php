<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'ruc',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'country',
        'credit_limit',
        'credit_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean',
    ];
}
