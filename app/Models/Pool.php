<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pool extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'created_by',
        'client_id',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by'); 
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'pool_employees'); 
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
