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
        Schema::table('ads_shopee', function (Blueprint $table) {
            $table->string('jenis_ads')->nullable()->after('kode_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_shopee', function (Blueprint $table) {
            $table->dropColumn('jenis_ads');
        });
    }
};
