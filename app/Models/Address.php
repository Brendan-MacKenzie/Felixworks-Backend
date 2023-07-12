<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'street_name',
        'number',
        'zip_code',
        'city',
        'country',
        'model_type',
        'model_id',
        'created_by',
    ];

    public function location()
    {
        return $this->hasOne(Location::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postings()
    {
        return $this->hasMany(Posting::class, 'address_id');
    }

    public function office()
    {
        return $this->morphTo(Office::class, 'address_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function workplaces()
    {
        return $this->hasMany(Workplace::class, 'address_id');
    }
}
