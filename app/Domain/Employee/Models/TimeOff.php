<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_off_type',
        'date',
        'request_type',
        'reason',
        'delegate_to',
        'file',
        'employee_id',
        'status_approval',
    ];
}
