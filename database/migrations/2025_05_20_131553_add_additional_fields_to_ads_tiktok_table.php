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
            $table->decimal('biaya_bersih', 15, 2)->nullable()->change();
            $table->decimal('roi', 8, 2)->nullable()->change();
            $table->string('mata_uang')->nullable()->after('account_name');
            $table->string('id_campaign')->nullable()->after('mata_uang');
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
            $table->dropColumn('id_campaign');
        });
    }
};
