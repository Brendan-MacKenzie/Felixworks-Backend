<?php

namespace App\Models;

use App\Helpers\RedisHelper;
use App\Enums\PlacementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Placement extends Model
{
    use HasFactory;
    use RedisHelper;

    protected $fillable = [
        'status',
        'posting_id',
        'workplace_id',
        'placement_type_id',
        'employee_id',
        'created_by',
        'report_at',
        'start_at',
        'end_at',
        'hours',
        'registered_at',
    ];

    protected $casts = [
        'report_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        self::created(function ($model) {
            self::staticSyncRedisPosting($model->posting, 'created');
        });

        self::updated(function ($model) {
            self::staticSyncRedisPosting($model->posting, 'updated');
        });

        self::deleted(function ($model) {
            self::staticSyncRedisPosting($model->posting, 'deleted');
        });
    }

    public function scopeOpen($query)
    {
        return $query->where('status', PlacementStatus::Open);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', PlacementStatus::Confirmed);
    }

    public function scopeOpenOrConfirmed($query)
    {
        return $query->whereIn('status', [PlacementStatus::Open, PlacementStatus::Confirmed]);
    }

    public function scopeRegistered($query)
    {
        return $query->where('status', PlacementStatus::Registered)->whereNotNull('registered_at');
    }

    public function scopeConfirmedOrRegistered($query)
    {
        return $query->whereIn('status', [PlacementStatus::Confirmed, PlacementStatus::Registered]);
    }

    public function posting()
    {
        return $this->belongsTo(Posting::class, 'posting_id');
    }

    public function workplace()
    {
        return $this->belongsTo(Workplace::class, 'workplace_id');
    }

    public function placementType()
    {
        return $this->belongsTo(PlacementType::class, 'placement_type_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agency()
    {
        return $this->hasOneThrough(Agency::class, Employee::class);
    }

    public function declarations()
    {
        return $this->hasMany(Declaration::class, 'placement_id');
    }
}
