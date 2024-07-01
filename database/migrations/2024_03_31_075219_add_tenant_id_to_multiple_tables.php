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
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('sales_by_channels', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('marketing_recaps', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('ad_spent_market_places', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });

        Schema::table('ad_spent_social_media', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained();
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('sales_by_channels', function (Blueprint $table) {
            $table->dropForeign('sales_by_channels_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('marketings', function (Blueprint $table) {
            $table->dropForeign('marketings_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('marketing_recaps', function (Blueprint $table) {
            $table->dropForeign('marketing_recaps_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign('visits_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('ad_spent_market_places', function (Blueprint $table) {
            $table->dropForeign('ad_spent_market_places_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });

        Schema::table('ad_spent_social_media', function (Blueprint $table) {
            $table->dropForeign('ad_spent_social_media_tenant_id_foreign');
            $table->dropColumn('tenant_id');
        });
    }
};
