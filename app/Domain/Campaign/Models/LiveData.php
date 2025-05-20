<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\User\Models\User;

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
        'employee_id',
        'sales_channel_id',
    ];

    protected $casts = [
        'date' => 'date',
        'dilihat' => 'integer',
        'penonton_tertinggi' => 'integer',
        'rata_rata_durasi' => 'integer',
        'komentar' => 'integer',
        'pesanan' => 'integer',
        'penjualan' => 'decimal:2',
        'sales_channel_id' => 'integer',
    ];

    public function salesChannel()
    {
        return $this->belongsTo(SalesChannel::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id', 'employee_id');
    }
}