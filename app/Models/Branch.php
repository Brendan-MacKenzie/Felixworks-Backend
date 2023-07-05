<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dresscode',
        'briefing',
        'client_id',
        'address_id',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function coordinators()
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'model')->where('type', AddressType::Branch);
    }

    public function workAddresses()
    {
        return $this->morphMany(Address::class, 'model')->where('type', AddressType::Default);
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function postings()
    {
        return $this->hasManyThrough(Posting::class, Address::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'branch_employees');
    }

    public function pools()
    {
        return $this->hasMany(Pool::class, 'branch_id');
    }
}
