<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model // Using ParentModel because 'Parent' is a reserved keyword in some contexts or at least confusing
{
    use BelongsToTenant;

    protected $table = 'parents';

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'document_number',
        'phone',
        'email'
    ];

    public function students()
    {
        return $this->belongsToMany(Customer::class, 'parent_student', 'parent_id', 'customer_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }
}
