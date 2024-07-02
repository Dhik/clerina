<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{

    protected $table = 'shifts';
    protected $fillable = [
        'shift_name',
        'schedule_in',
        'schedule_out',
    ];
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
