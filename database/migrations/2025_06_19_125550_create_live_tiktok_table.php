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
        Schema::create('live_tiktok', function (Blueprint $table) {
            $table->id();
            $table->integer('gmv_live')->nullable();
            $table->date('date')->nullable();
            $table->integer('pesanan')->nullable();
            $table->integer('tayangan')->nullable();
            $table->integer('gpm')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_tiktok');
    }
};
