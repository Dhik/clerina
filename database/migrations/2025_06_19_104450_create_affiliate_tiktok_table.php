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
        Schema::create('affiliate_tiktok', function (Blueprint $table) {
            $table->id();
            $table->string('creator_username')->nullable();
            $table->integer('affiliate_gmv')->nullable();
            $table->integer('affiliate_live_gmv')->nullable();
            $table->integer('affiliate_shoppable_video')->nullable();
            $table->integer('affiliate_product_card_gmv')->nullable();
            $table->integer('affiliate_products_sold')->nullable();
            $table->integer('items_sold')->nullable();
            $table->integer('est_commission')->nullable();
            $table->integer('avg_order_value')->nullable();
            $table->integer('affiliate_orders')->nullable();
            $table->string('ctr')->nullable();
            $table->integer('product_impressions')->nullable();
            $table->integer('avg_affiliate_customers')->nullable();
            $table->integer('affiliate_live_streams')->nullable();
            $table->integer('open_collaboration_gmv')->nullable();
            $table->integer('open_collaboration_est')->nullable();
            $table->integer('affiliate_refunded_gmv')->nullable();
            $table->integer('affiliate_items_refunded')->nullable();
            $table->integer('affiliate_followers')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_tiktok');
    }
};
