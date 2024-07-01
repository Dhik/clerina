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
        Schema::table('funnels', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('funnel_recaps', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('funnel_totals', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funnels', function (Blueprint $table) {
            $table->dropForeign('funnels_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('funnel_recaps', function (Blueprint $table) {
            $table->dropForeign('funnel_recaps_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('funnel_totals', function (Blueprint $table) {
            $table->dropForeign('funnel_totals_foreign');
            $table->dropColumn('tenant_id');
        });
    }
};
