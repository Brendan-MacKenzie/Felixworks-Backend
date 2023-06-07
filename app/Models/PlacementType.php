<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'placement_type_id');
    }

    
}
