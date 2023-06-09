<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'agency_id',
        'external_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'avatar_id',
        'drivers_license',
        'car',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'datetime',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Media::class, 'avatar_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pools()
    {
        return $this->belongsToMany(Pool::class, 'pool_employees');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'employee_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_employees');
    }
}
