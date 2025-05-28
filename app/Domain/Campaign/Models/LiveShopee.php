<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\User\Models\User;

class LiveShopee extends Model
{
    use HasFactory;

    protected $table = 'live_shopee';
    
    protected $fillable = [
        'date',
        'user_id',
        'no',
        'nama_livestream',
        'start_time',
        'durasi',
        'penonton_aktif',
        'komentar',
        'tambah_ke_keranjang',
        'rata_rata_durasi_ditonton',
        'penonton',
        'pesanan_dibuat',
        'pesanan_siap_dikirim',
        'produk_terjual_dibuat',
        'produk_terjual_siap_dikirim',
        'penjualan_dibuat',
        'penjualan_siap_dikirim',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'durasi' => 'integer',
        'penonton_aktif' => 'integer',
        'komentar' => 'integer',
        'tambah_ke_keranjang' => 'integer',
        'rata_rata_durasi_ditonton' => 'decimal:2',
        'penonton' => 'integer',
        'pesanan_dibuat' => 'integer',
        'pesanan_siap_dikirim' => 'integer',
        'produk_terjual_dibuat' => 'integer',
        'produk_terjual_siap_dikirim' => 'integer',
        'penjualan_dibuat' => 'decimal:2',
        'penjualan_siap_dikirim' => 'decimal:2',
    ];
}