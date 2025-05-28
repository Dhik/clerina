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
        Schema::create('live_shopee', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('user_id')->nullable();
            $table->string('no')->nullable();
            $table->string('nama_livestream')->nullable();
            $table->time('start_time')->nullable();
            $table->integer('durasi')->nullable(); // in minutes
            $table->integer('penonton_aktif')->nullable();
            $table->integer('komentar')->nullable();
            $table->integer('tambah_ke_keranjang')->nullable();
            $table->decimal('rata_rata_durasi_ditonton', 8, 2)->nullable(); // in minutes with 2 decimal places
            $table->integer('penonton')->nullable();
            $table->integer('pesanan_dibuat')->nullable();
            $table->integer('pesanan_siap_dikirim')->nullable();
            $table->integer('produk_terjual_dibuat')->nullable();
            $table->integer('produk_terjual_siap_dikirim')->nullable();
            $table->decimal('penjualan_dibuat', 15, 2)->nullable(); // for currency values
            $table->decimal('penjualan_siap_dikirim', 15, 2)->nullable(); // for currency values
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_shopee');
    }
};
