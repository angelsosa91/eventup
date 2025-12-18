<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
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
        'contact_person',
        'bank_name',
        'bank_account',
        'payment_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'payment_days' => 'integer',
        'is_active' => 'boolean',
    ];
}
