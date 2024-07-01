<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift_id',
        'compensation',
        'before_shift_overtime_duration',
        'before_shift_break_duration',
        'after_shift_overtime_duration',
        'after_shift_break_duration',
        'note',
        'file',
        'status_approval',
        'employee_id', 
    ];
}
