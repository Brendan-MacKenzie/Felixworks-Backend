<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address_id',
        'start_at',
        'dresscode',
        'briefing',
        'information',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function address()
    {
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'posting_id'); 
    }

    public function commitments()
    {
        return $this->belongsToMany(Agency::class, 'commitments')->withPivot(['amount', 'created_by']);
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'posting_agencies');
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
