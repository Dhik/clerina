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
        Schema::table('bcg_product', function (Blueprint $table) {
            $table->integer('biaya_ads')->nullable()->after('harga');
            $table->integer('omset_penjualan')->nullable()->after('biaya_ads');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bcg_product', function (Blueprint $table) {
            $table->dropColumn(['biaya_ads', 'omset_penjualan']);
        });
    }
};
