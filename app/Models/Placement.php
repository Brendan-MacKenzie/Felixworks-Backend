<?php

namespace App\Models;

use Carbon\Carbon;
use App\Enums\PlacementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Placement extends Model
{
    use HasFactory;

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

    public function scopeFuture($query)
    {
        return $query->where('start_at', '>=', Carbon::now());
    }

    public function posting()
    {
        return $this->belongsTo(Posting::class, 'posting_id');
    }

    public function workplace()
    {
        return $this->belongsTo(Workplace::class, 'workplace_id')->withTrashed();
    }

    public function placementType()
    {
        return $this->belongsTo(PlacementType::class, 'placement_type_id')->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function declarations()
    {
        return $this->hasMany(Declaration::class, 'placement_id');
    }
}
