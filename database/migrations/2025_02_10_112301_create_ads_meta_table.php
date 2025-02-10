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
        Schema::create('ads_meta', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('amount_spent')->nullable();
            $table->bigInteger('impressions')->nullable();
            $table->double('content_views_shared_items', 15, 2)->nullable();
            $table->double('adds_to_cart_shared_items', 15, 2)->nullable();
            $table->double('purchases_shared_items', 15, 2)->nullable();
            $table->double('purchases_conversion_value_shared_items', 20, 2)->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_meta');
    }
};
