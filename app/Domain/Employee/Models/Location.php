<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_name',
        'lat',
        'long',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
