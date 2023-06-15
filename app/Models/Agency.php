<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'brand_color',
        'logo_id',
        'created_by',
    ];

    public function logo()
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agents()
    {
        return $this->hasMany(User::class, 'agency_id');
    }

    public function postings()
    {
        return $this->hasMany(Posting::class, 'agency_id');
    }

    public function commitments()
    {
        return $this->belongsToMany(Posting::class, 'commitments')->withPivot(['amount', 'created_by']);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'agency_id');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function offices()
    {
        return $this->hasMany(Office::class, 'agency_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'agency_id');
    }
}
