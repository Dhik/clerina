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
        Schema::create('bcg_product', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->integer('tenant_id')->nullable();
            $table->string('kode_produk')->nullable();
            $table->string('nama_produk')->nullable();
            $table->integer('visitor')->nullable();
            $table->integer('jumlah_atc')->nullable();
            $table->integer('jumlah_pembeli')->nullable();
            $table->integer('qty_sold')->nullable();
            $table->integer('sales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bcg_product');
    }
};
