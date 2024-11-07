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
        Schema::create('customers_analysis', function (Blueprint $table) {
            $table->id();
            $table->datetime('tanggal_pesanan_dibuat');
            $table->string('nama_penerima')->nullable();
            $table->string('produk')->nullable();
            $table->integer('qty')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->unsignedBigInteger('social_media_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_analysis');
    }
};
