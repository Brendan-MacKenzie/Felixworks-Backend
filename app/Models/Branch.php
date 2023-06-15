<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dresscode',
        'briefing',
        'client_id',
        'address_id',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coordinators()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function addresses()
    {
        return $this->hasMany(Client::class, 'branch_id');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function postings()
    {
        return $this->hasManyThrough(Posting::class, Address::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'branch_employees');
    }
}
