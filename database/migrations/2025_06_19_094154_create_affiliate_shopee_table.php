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
        Schema::create('affiliate_shopee', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('affiliate_id')->nullable();
            $table->string('nama_affiliate')->nullable();
            $table->string('username_affiliate')->nullable();
            $table->integer('omzet_penjualan')->nullable();
            $table->integer('produk_terjual')->nullable();
            $table->integer('pesanan')->nullable();
            $table->integer('clicks')->nullable();
            $table->integer('estimasi_komisi')->nullable();
            $table->decimal('roi', 10, 2)->nullable();
            $table->integer('total_pembeli')->nullable();
            $table->integer('pembeli_baru')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_shopee');
    }
};
