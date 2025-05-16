<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads_shopee', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('urutan')->nullable();
            $table->text('nama_iklan')->nullable();
            $table->string('status')->nullable();
            $table->string('tampilan_iklan')->nullable();
            $table->string('mode_bidding')->nullable();
            $table->string('penempatan_iklan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->string('tanggal_selesai')->nullable();
            $table->integer('dilihat')->nullable();
            $table->integer('jumlah_klik')->nullable();
            $table->integer('konversi')->nullable();
            $table->integer('produk_terjual')->nullable();
            $table->integer('omzet_penjualan')->nullable();
            $table->integer('biaya')->nullable();
            $table->float('efektivitas_iklan')->nullable();
            $table->date('date')->nullable();
            $table->string('kode_produk')->nullable();
            $table->string('sku_induk')->nullable();
            $table->integer('pengunjung_produk_kunjungan')->nullable();
            $table->integer('halaman_produk_dilihat')->nullable();
            $table->integer('pengunjung_melihat_tanpa_membeli')->nullable();
            $table->integer('klik_pencarian')->nullable();
            $table->integer('suka')->nullable();
            $table->integer('pengunjung_produk_menambahkan_produk_ke_keranjang')->nullable();
            $table->integer('dimasukan_ke_keranjang_produk')->nullable();
            $table->integer('total_pembeli_pesanan_dibuat')->nullable();
            $table->integer('produk_pesanan_dibuat')->nullable();
            $table->integer('produk_pesanan_siap_dikirim')->nullable();
            $table->integer('total_pembeli_pesanan_siap_dikirim')->nullable();
            $table->integer('total_penjualan_pesanan_dibuat_idr')->nullable();
            $table->integer('penjualan_pesanan_siap_dikirim_idr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_shopee');
    }
};
