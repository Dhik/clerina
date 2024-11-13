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
        Schema::create('customers_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_customers_facts'); 
            $table->string('produk')->nullable();
            $table->integer('qty')->nullable();
            $table->timestamp('tanggal_pesanan_dibuat')->nullable();
            $table->foreign('id_customers_facts')->references('id')->on('customers_facts')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_dimensions');
    }
};
