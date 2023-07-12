<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function postings()
    {
        return $this->morphedByMany(Posting::class, 'model', 'region_models');
    }

    public function offices()
    {
        return $this->morphedByMany(Office::class, 'model', 'region_models');
    }

    public function agencies()
    {
        return $this->morphedByMany(Agency::class, 'model', 'region_models');
    }

    public function locations()
    {
        return $this->morphedByMany(Location::class, 'model', 'region_models');
    }
}
