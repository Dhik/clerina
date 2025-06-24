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
        Schema::table('content_plan', function (Blueprint $table) {
            $table->string('talent_fix')->nullable();
            $table->dateTime('booking_talent_date')->nullable();
            $table->dateTime('booking_venue_date')->nullable();
            $table->dateTime('production_date')->nullable();
            
            // Change target_posting_date from date to datetime
            $table->dateTime('target_posting_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_plan', function (Blueprint $table) {
            $table->dropColumn([
                'talent_fix',
                'booking_talent_date',
                'booking_venue_date',
                'production_date'
            ]);
            
            // Revert target_posting_date back to date
            $table->date('target_posting_date')->nullable()->change();
        });
    }
};
