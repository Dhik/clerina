<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestChangeShift extends Model 
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'starts_shift',
        'end_shift',
        'status_approval',
        'note',
        'clocktime',
        'file',
    ];
}