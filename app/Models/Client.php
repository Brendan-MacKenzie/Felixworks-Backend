<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'client_id');
    }

    public function coordinators()
    {
        return $this->hasMany(User::class, 'client_id');
    }
}
