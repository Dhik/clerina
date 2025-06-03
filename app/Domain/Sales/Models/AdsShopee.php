<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdsShopee extends Model
{
    protected $table = 'ads_shopee';

    protected $fillable = [
        'urutan',
        'nama_iklan',
        'status',
        'tampilan_iklan',
        'mode_bidding',
        'penempatan_iklan',
        'tanggal_mulai',
        'tanggal_selesai',
        'dilihat',
        'jumlah_klik',
        'konversi',
        'produk_terjual',
        'omzet_penjualan',
        'biaya',
        'efektivitas_iklan',
        'date',
        'kode_produk',
        'sku_induk',
        'pengunjung_produk_kunjungan',
        'halaman_produk_dilihat',
        'pengunjung_melihat_tanpa_membeli',
        'klik_pencarian',
        'suka',
        'pengunjung_produk_menambahkan_produk_ke_keranjang',
        'dimasukan_ke_keranjang_produk',
        'total_pembeli_pesanan_dibuat',
        'produk_pesanan_dibuat',
        'produk_pesanan_siap_dikirim',
        'total_pembeli_pesanan_siap_dikirim',
        'total_penjualan_pesanan_dibuat_idr',
        'penjualan_pesanan_siap_dikirim_idr',
        'tenant_id',
        'jenis_ads',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'tanggal_mulai' => 'date',
        'dilihat' => 'integer',
        'jumlah_klik' => 'integer',
        'konversi' => 'integer',
        'produk_terjual' => 'integer',
        'omzet_penjualan' => 'integer',
        'biaya' => 'integer',
        'efektivitas_iklan' => 'float',
        'date' => 'date',
        'pengunjung_produk_kunjungan' => 'integer',
        'halaman_produk_dilihat' => 'integer',
        'pengunjung_melihat_tanpa_membeli' => 'integer',
        'klik_pencarian' => 'integer',
        'suka' => 'integer',
        'pengunjung_produk_menambahkan_produk_ke_keranjang' => 'integer',
        'dimasukan_ke_keranjang_produk' => 'integer',
        'total_pembeli_pesanan_dibuat' => 'integer',
        'produk_pesanan_dibuat' => 'integer',
        'produk_pesanan_siap_dikirim' => 'integer',
        'total_pembeli_pesanan_siap_dikirim' => 'integer',
        'total_penjualan_pesanan_dibuat_idr' => 'integer',
        'penjualan_pesanan_siap_dikirim_idr' => 'integer',
    ];
}