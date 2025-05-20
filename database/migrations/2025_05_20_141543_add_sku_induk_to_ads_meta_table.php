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
            $table->string('sku_induk')->nullable()->after('pic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_meta', function (Blueprint $table) {
            $table->dropColumn('sku_induk');
        });
    }
};
