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
        Schema::create('spent_targets', function (Blueprint $table) {
            $table->id();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('kol_percentage', 5, 2)->nullable(); 
            $table->decimal('ads_percentage', 5, 2)->nullable(); 
            $table->decimal('creative_percentage', 5, 2)->nullable(); 
            $table->decimal('other_percentage', 5, 2)->nullable(); 
            $table->decimal('affiliate_percentage', 5, 2)->nullable(); 
            $table->string('month')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spent_targets');
    }
};
