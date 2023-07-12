<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlacementType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'position',
        'location_id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'placement_type_id');
    }

    public function defaultWorkplaces()
    {
        return $this->belongsToMany(Workplace::class, 'placement_type_workplaces');
    }
}
