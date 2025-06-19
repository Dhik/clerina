<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveTiktok extends Model
{
    use HasFactory;

    protected $table = 'live_tiktok';
    
    protected $fillable = [
        'gmv_live',
        'date',
        'pesanan',
        'tayangan',
        'gpm',
    ];

    protected $casts = [
        'date' => 'date',
        'gmv_live' => 'integer',
        'pesanan' => 'integer',
        'tayangan' => 'integer',
        'gpm' => 'integer',
    ];
}