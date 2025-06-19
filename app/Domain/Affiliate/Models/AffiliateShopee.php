<?php

namespace App\Domain\Affiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\User\Models\User;

class AffiliateShopee extends Model
{
    use HasFactory;

    protected $table = 'affiliate_shopee';
    
    protected $fillable = [
        'affiliate_id',
        'nama_affiliate',
        'username_affiliate',
        'omzet_penjualan',
        'produk_terjual',
        'pesanan',
        'clicks',
        'estimasi_komisi',
        'roi',
        'total_pembeli',
        'pembeli_baru',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'omzet_penjualan' => 'integer',
        'produk_terjual' => 'integer',
        'pesanan' => 'integer',
        'clicks' => 'integer',
        'estimasi_komisi' => 'integer',
        'roi' => 'decimal:2',
        'total_pembeli' => 'integer',
        'pembeli_baru' => 'integer',
    ];
}