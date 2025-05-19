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
        Schema::table('live_data', function (Blueprint $table) {
            $table->string('employee_id')->nullable();
            $table->integer('sales_channel_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_data', function (Blueprint $table) {
            $table->dropColumn('employee_id');
            $table->dropColumn('sales_channel_id');
        });
    }
};
