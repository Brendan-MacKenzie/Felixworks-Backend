<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workplace extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'address_id');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'workplace_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
