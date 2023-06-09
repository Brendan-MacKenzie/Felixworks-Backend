<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Posting extends Model
{
    use HasFactory;

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
    }

    public function address()
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->belongsToMany(Agency::class, 'commitments')->withPivot(['amount', 'created_by']);
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class, 'posting_agencies');
    }

    public function sectors()
    {
        return $this->morphToMany(Sector::class, 'model', 'sector_models');
    }

    public function regions()
    {
        return $this->morphToMany(Region::class, 'model', 'region_models');
    }
}
