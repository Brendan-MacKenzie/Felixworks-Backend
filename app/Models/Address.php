<?php

namespace App\Models;

use App\Helpers\RedisHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;
    use SoftDeletes;
    use RedisHelper;

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

    protected static function booted(): void
    {
        self::updated(function ($model) {
            foreach ($model->postings as $posting) {
                self::staticSyncRedisPosting($posting, 'updated');
            }
        });
    }

    public function branch()
    {
        return $this->hasOne(Branch::class, 'address_id');
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
