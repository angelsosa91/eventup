<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AcademicShift extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Customer::class, 'shift_id');
    }
}
