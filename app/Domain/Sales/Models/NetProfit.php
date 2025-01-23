<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetProfit extends Model
{
    protected $table = 'net_profits';
    protected $fillable = [
        'date',
        'sales',
        'marketing',
        'spent_kol',
        'affiliate',
        'operasional',
        'hpp',
    ];
    protected $casts = [
        'date' => 'date'
    ];
}
