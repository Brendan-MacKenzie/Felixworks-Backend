<?php

namespace App\Models;

use FlexFlux\Encryptor\Encryptable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agency extends Model
{
    use HasFactory;
    use Encryptable;

    protected $fillable = [
        'name',
        'full_name',
        'email',
        'base_rate',
        'brand_color',
        'api_key',
        'ip_address',
        'webhook',
        'webhook_key',
        'logo_id',
        'created_by',
    ];

    protected $encryptable = [
        'webhook',
    ];

    protected $hidden = [
        'api_key',
        'ip_address',
        'webhook',
        'webhook_key',
    ];

    public function toArray()
    {
        $this->checkModelPermissions();

        return parent::toArray();
    }

    private function checkModelPermissions()
    {
        if (
            auth()->user() &&
            auth()->user()->hasRole('admin')
        ) {
            $this->makeVisible([
                'ip_address',
                'webhook',
            ]);
        }
    }

    public function logo()
    {
        return $this->belongsTo(Media::class, 'logo_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agents()
    {
        return $this->hasMany(User::class, 'agency_id');
    }

    public function postings()
    {
        return $this->hasMany(Posting::class, 'agency_id');
    }

    public function commitments()
    {
        return $this->belongsToMany(Posting::class, 'commitments')->withPivot(['amount', 'created_by']);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'agency_id');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }

    public function offices()
    {
        return $this->hasMany(Office::class, 'agency_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'agency_id');
    }
}
