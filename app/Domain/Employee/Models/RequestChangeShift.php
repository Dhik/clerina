<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestChangeShift extends Model 
{
    use HasFactory;

    protected $fillable = [
        'date',
        'starts_shift_id',
        'change_shift_id',
        'status_approval',
        'note',
        'clocktime',
        'file',
    ];
}