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
        Schema::create('live_shopee_product', function (Blueprint $table) {
            $table->id();
            $table->date('periode_data')->nullable();
            $table->string('user_id')->nullable();
            $table->integer('ranking')->nullable();
            $table->text('produk')->nullable();
            $table->integer('klik_produk')->nullable();
            $table->integer('tambah_ke_keranjang')->nullable();
            $table->integer('pesanan_dibuat')->nullable();
            $table->integer('pesanan_siap_dikirim')->nullable();
            $table->integer('produk_terjual_siap_dikirim')->nullable();
            $table->decimal('penjualan_dibuat', 15, 2)->nullable();
            $table->decimal('penjualan_siap_dikirim', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_shopee_product');
    }
};
