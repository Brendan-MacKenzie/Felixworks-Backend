<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'report_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

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
}
