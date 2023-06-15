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
        'created_by',
    ];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'address_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_addresses');
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
        return $this->hasOne(Office::class, 'address_id');
    }
}
