<?php

namespace App\Models;

use App\Helpers\RedisHelper;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Posting extends Model
{
    use HasFactory;
    use RedisHelper;

    protected $fillable = [
        'name',
        'address_id',
        'start_at',
        'dresscode',
        'briefing',
        'information',
        'created_by',
        'cancelled_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scopes.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);

        self::created(function ($model) {
            self::staticSyncRedisPosting($model, 'created');
        });

        self::updated(function ($model) {
            self::staticSyncRedisPosting($model, 'updated');
        });

        self::deleted(function ($model) {
            self::staticSyncRedisPosting($model, 'deleted');
        });
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

   public function workAddress()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function placements()
    {
        return $this->hasMany(Placement::class, 'posting_id');
    }

    public function commitments()
    {
        return $this->hasMany(Commitment::class, 'posting_id');
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'posting_agencies');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }
}
