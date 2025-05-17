<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveData extends Model
{
    use HasFactory;

    protected $table = 'live_data';
    
    protected $fillable = [
        'date',
        'shift',
        'dilihat',
        'penonton_tertinggi',
        'rata_rata_durasi',
        'komentar',
        'pesanan',
        'penjualan',
    ];

    protected $casts = [
        'date' => 'date',
        'dilihat' => 'integer',
        'penonton_tertinggi' => 'integer',
        'rata_rata_durasi' => 'integer',
        'komentar' => 'integer',
        'pesanan' => 'integer',
        'penjualan' => 'decimal:2',
    ];
}