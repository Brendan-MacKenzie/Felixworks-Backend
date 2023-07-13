<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'avatar_uuid');
    }

    public function agency()
    {
        return $this->hasOne(Agency::class, 'logo_uuid');
    }
}
