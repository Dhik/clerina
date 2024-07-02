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
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('amount_discount');
            $table->dropColumn('discount_price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('turnover_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('amount_discount');
            $table->bigInteger('discount_price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->bigInteger('turnover_discount');
        });
    }
};
