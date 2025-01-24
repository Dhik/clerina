<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalSpent extends Model
{
    protected $fillable = [
        'spent',
        'month',
        'year',
        'tenant_id',
    ];
}
