<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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

    public function clients()
    {
        return $this->morphedByMany(Client::class, 'model', 'region_models');
    }
}
