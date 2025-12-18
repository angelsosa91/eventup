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
        'family_name',
        'grade_id',
        'section_id',
        'shift_id',
        'bachillerato_id',
        'delegate_id',
        'billing_name',
        'billing_ruc',
        'billing_email',
        'budget_type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];


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

    public function delegate()
    {
        return $this->belongsTo(Delegate::class, 'delegate_id');
    }

}
