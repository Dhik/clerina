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
            $table->tinyInteger('is_dormant')->default(0)->after('is_joined');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers_analysis', function (Blueprint $table) {
            $table->dropColumn('is_dormant');
        });
    }
};
