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
        Schema::create('net_profits', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->decimal('sales', 15, 2)->nullable();
            $table->decimal('marketing', 15, 2)->nullable();
            $table->decimal('spent_kol', 15, 2)->nullable();
            $table->decimal('affiliate', 15, 2)->nullable();
            $table->decimal('operasional', 15, 2)->nullable();
            $table->decimal('hpp', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('net_profits');
    }
};
