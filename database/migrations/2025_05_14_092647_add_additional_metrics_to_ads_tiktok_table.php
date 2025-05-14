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
            $table->string('primary_status')->nullable()->after('pic');
            $table->decimal('campaign_budget', 15, 2)->nullable()->after('primary_status');
            $table->bigInteger('product_page_views')->nullable()->after('campaign_budget');
            $table->bigInteger('items_purchased')->nullable()->after('product_page_views');
            $table->decimal('cpm', 15, 2)->nullable()->after('items_purchased');
            $table->decimal('cpc', 15, 2)->nullable()->after('cpm');
            $table->decimal('cost_per_purchase', 15, 2)->nullable()->after('cpc');
            $table->decimal('ctr', 10, 4)->nullable()->after('cost_per_purchase');
            $table->decimal('purchase_rate', 10, 4)->nullable()->after('ctr');
            $table->decimal('average_order_value', 15, 2)->nullable()->after('purchase_rate');
            $table->bigInteger('live_views')->nullable()->after('average_order_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_tiktok', function (Blueprint $table) {
            $table->dropColumn([
                'primary_status',
                'campaign_budget',
                'product_page_views',
                'items_purchased',
                'cpm',
                'cpc',
                'cost_per_purchase',
                'ctr',
                'purchase_rate',
                'average_order_value',
                'live_views'
            ]);
        });
    }
};
