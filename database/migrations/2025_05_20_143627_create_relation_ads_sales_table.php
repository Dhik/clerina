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
        Schema::create('relation_ads_sales', function (Blueprint $table) {
            $table->id();
            $table->decimal('sales', 15, 2)->nullable();
            $table->decimal('marketing', 15, 2)->nullable();
            $table->string('sku')->nullable();
            $table->date('date');
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relation_ads_sales');
    }
};
