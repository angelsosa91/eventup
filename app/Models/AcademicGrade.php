<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AcademicGrade extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Customer::class, 'grade_id');
    }
}

class AcademicSection extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Customer::class, 'section_id');
    }
}

class AcademicShift extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Customer::class, 'shift_id');
    }
}

class AcademicBachillerato extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'is_active'];

    public function students()
    {
        return $this->hasMany(Customer::class, 'bachillerato_id');
    }
}
