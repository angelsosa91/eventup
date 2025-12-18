<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'code',
        'swift_code',
        'country',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
