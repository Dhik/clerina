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
        Schema::table('ads_meta', function (Blueprint $table) {
            $table->string('campaign_name')->nullable();
            $table->double('link_clicks', 15, 2)->nullable()->after('impressions');
            $table->string('kategori_produk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_meta', function (Blueprint $table) {
            $table->dropColumn('campaign_name');
            $table->dropColumn('link_clicks');
            $table->dropColumn('kategori_produk');
        });
    }
};
