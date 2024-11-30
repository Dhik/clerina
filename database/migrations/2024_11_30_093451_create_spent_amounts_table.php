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
        Schema::create('spent_amounts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('activation_spent', 10, 2)->nullable(); 
            $table->decimal('creative_spent', 10, 2)->nullable(); 
            $table->decimal('free_product_spent', 10, 2)->nullable(); 
            $table->decimal('other_spent', 10, 2)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spent_amounts');
    }
};
