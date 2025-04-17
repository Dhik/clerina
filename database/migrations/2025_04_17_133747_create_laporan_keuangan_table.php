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
        Schema::create('laporan_keuangan', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->decimal('sales', 15, 2)->nullable();
            $table->decimal('hpp', 15, 2)->nullable();
            $table->decimal('balance_amount', 15, 2)->nullable();
            $table->decimal('gross_revenue', 15, 2)->nullable();
            $table->decimal('fee_admin', 15, 2)->nullable();
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->foreign('sales_channel_id')
                ->references('id')
                ->on('sales_channels')
                ->onDelete('set null');
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_keuangan');
    }
};