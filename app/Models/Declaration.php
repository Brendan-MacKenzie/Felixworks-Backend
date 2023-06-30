<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Declaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'total',
        'placement_id',
        'created_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function placement()
    {
        return $this->belongsTo(Placement::class, 'placement_id');
    }
}
