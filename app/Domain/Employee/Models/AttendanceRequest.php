<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift_id',
        'clock_in',
        'clock_out',
        'work_note',
        'status_approval',
        'employee_id', 
        'file',
    ];
}
