<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'created_by',
        'location_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'pool_employees');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
