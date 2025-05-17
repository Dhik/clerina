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
        Schema::create('live_data', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('shift')->nullable();
            $table->integer('dilihat')->default(0);
            $table->integer('penonton_tertinggi')->default(0);
            $table->integer('rata_rata_durasi')->default(0);
            $table->integer('komentar')->default(0);
            $table->integer('pesanan')->default(0);
            $table->decimal('penjualan', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_data');
    }
};
