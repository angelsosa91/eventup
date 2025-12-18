<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'name',
        'ruc',
        'birth_date',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'country',
        'family_id',
        'grade_id',
        'section_id',
        'shift_id',
        'bachillerato_id',
        'credit_limit',
        'credit_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function grade()
    {
        return $this->belongsTo(AcademicGrade::class, 'grade_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function shift()
    {
        return $this->belongsTo(AcademicShift::class, 'shift_id');
    }

    public function bachillerato()
    {
        return $this->belongsTo(AcademicBachillerato::class, 'bachillerato_id');
    }

    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'parent_student', 'customer_id', 'parent_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }
}
