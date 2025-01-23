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
        Schema::table('customers_analysis', function (Blueprint $table) {
            $table->string('status_customer')->nullable()->after('is_joined');
            $table->string('which_hp')->nullable()->after('status_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers_analysis', function (Blueprint $table) {
            $table->dropColumn(['status_customer', 'which_hp']);
        });
    }
};
