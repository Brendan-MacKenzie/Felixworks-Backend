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
        'briefing',
        'dresscode',
        'position',
        'model_type',
        'model_id',
        'created_by',
    ];

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

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'address_employees');
    }

    public function defaultAgencies()
    {
        return $this->morphToMany(Agency::class, 'model', 'agency_models');
    }

    public static function getMaxPosition($locationId)
    {
        return self::where('model_type', Location::class)
                    ->where('model_id', $locationId)
                    ->max('position');
    }
}
