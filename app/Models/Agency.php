<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'full_name',
        'code',
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

    public function sectors()
    {
        return $this->morphToMany(Sector::class, 'model', 'sector_models');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function offices()
    {
        return $this->hasMany(Office::class, 'office_id');
    }
}
