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
        Schema::create('ads_monitoring', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('channel');
            
            // Target metrics
            $table->decimal('gmv_target', 15, 2)->nullable();
            $table->decimal('spent_target', 10, 2)->nullable();
            $table->decimal('roas_target', 8, 4)->nullable();
            $table->decimal('cpa_target', 10, 2)->nullable();
            $table->decimal('aov_to_cpa_target', 8, 4)->nullable();
            
            // Actual metrics
            $table->decimal('gmv_actual', 15, 2)->nullable();
            $table->decimal('spent_actual', 10, 2)->nullable();
            $table->decimal('roas_actual', 8, 4)->nullable();
            $table->decimal('cpa_actual', 10, 2)->nullable();
            $table->decimal('aov_to_cpa_actual', 8, 4)->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['date', 'channel']);
            $table->index('date');
            $table->index('channel');
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['date', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_monitoring');
    }
};
