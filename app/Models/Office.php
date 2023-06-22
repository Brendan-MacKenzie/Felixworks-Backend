<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'description',
        'website',
        'phone',
        'created_by',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'model')->where('type', AddressType::Office);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }
}
