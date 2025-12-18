<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'budget_type',
        'billing_name',
        'billing_ruc',
        'billing_email'
    ];

    public function students()
    {
        return $this->hasMany(Customer::class, 'family_id');
    }
}
