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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product')->nullable();
            $table->integer('stock')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->decimal('harga_markup', 15, 2)->nullable();
            $table->decimal('harga_cogs', 15, 2)->nullable();
            $table->decimal('harga_batas_bawah', 15, 2)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
