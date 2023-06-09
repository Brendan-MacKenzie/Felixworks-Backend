<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dresscode',
        'briefing',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coordinators()
    {
        return $this->hasMany(User::class, 'client_id');
    }

    public function addresses()
    {
        return $this->hasMany(Client::class, 'client_id');
    }

    public function sectors()
    {
        return $this->morphToMany(Sector::class, 'model', 'sector_models');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function postings()
    {
        return $this->hasMany(Posting::class, 'client_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'client_employees');
    }
}
