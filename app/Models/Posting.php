<?php

namespace App\Models;

use Carbon\Carbon;
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

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);
    }

    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    public function scopeFuture($query)
    {
        return $query->where('start_at', '>=', Carbon::now());
    }

    public function workAddress()
    {
        return $this->belongsTo(Address::class, 'address_id')->withTrashed();
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
