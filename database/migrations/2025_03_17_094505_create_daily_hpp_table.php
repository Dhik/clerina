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
        Schema::create('daily_hpp', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('HPP', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_hpp');
    }
};
