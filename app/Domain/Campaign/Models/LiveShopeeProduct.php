<?php

namespace App\Domain\Campaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveShopeeProduct extends Model
{
    use HasFactory;

    protected $table = 'live_shopee_product';
    
    protected $fillable = [
        'periode_data',
        'user_id',
        'ranking',
        'produk',
        'klik_produk',
        'tambah_ke_keranjang',
        'pesanan_dibuat',
        'pesanan_siap_dikirim',
        'produk_terjual_siap_dikirim',
        'penjualan_dibuat',
        'sku',
        'penjualan_siap_dikirim',
    ];

    protected $casts = [
        'periode_data' => 'date',
        'ranking' => 'integer',
        'klik_produk' => 'integer',
        'tambah_ke_keranjang' => 'integer',
        'pesanan_dibuat' => 'integer',
        'pesanan_siap_dikirim' => 'integer',
        'produk_terjual_siap_dikirim' => 'integer',
        'penjualan_dibuat' => 'decimal:2',
        'penjualan_siap_dikirim' => 'decimal:2',
    ];
}