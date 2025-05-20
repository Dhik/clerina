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
        Schema::table('ads_tiktok', function (Blueprint $table) {
            $table->float('biaya_bersih')->nullable()->after('amount_spent');
            $table->float('roi')->nullable()->after('cost_per_purchase');
            $table->string('mata_uang')->nullable()->after('account_name');
            $table->string('type')->nullable()->after('mata_uang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_tiktok', function (Blueprint $table) {
            $table->dropColumn('biaya_bersih');
            $table->dropColumn('roi');
            $table->dropColumn('mata_uang');
            $table->dropColumn('type');
        });
    }
};
