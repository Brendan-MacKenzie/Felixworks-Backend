<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'description',
        'website',
        'phone',
        'street_name',
        'number',
        'zip_code',
        'city',
        'country',
        'created_by',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sectors()
    {
        return $this->morphToMany(Sector::class, 'model', 'sector_models');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }
}
