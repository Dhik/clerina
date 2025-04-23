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
        Schema::create('ads_meta2', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('amount_spent')->nullable();
            $table->bigInteger('impressions')->nullable();
            $table->double('link_clicks', 15, 2)->nullable();
            $table->double('content_views_shared_items', 15, 2)->nullable();
            $table->double('adds_to_cart_shared_items', 15, 2)->nullable();
            $table->double('purchases_shared_items', 15, 2)->nullable();
            $table->double('purchases_conversion_value_shared_items', 20, 2)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
            $table->date('last_updated')->nullable();
            $table->date('new_created')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('kategori_produk')->nullable();
            $table->string('pic')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_meta2');
    }
};
