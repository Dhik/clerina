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
            $table->decimal('sales', 15, 2)->nullable();
            $table->decimal('marketing', 15, 2)->nullable();
            $table->decimal('spent_kol', 15, 2)->nullable();
            $table->decimal('affiliate', 15, 2)->nullable();
            $table->decimal('operasional', 15, 2)->nullable();
            $table->decimal('hpp', 15, 2)->nullable();
            $table->decimal('roas', 30, 2)->nullable();
            $table->bigInteger('visit')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->bigInteger('order')->nullable();
            $table->decimal('closing_rate', 30, 2)->nullable();
            $table->decimal('fee_packing', 15, 2)->nullable()->after('hpp');
            $table->decimal('romi', 30, 2)->nullable()->after('roas');
            $table->unsignedBigInteger('tenant_id')->nullable()->after('date');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->decimal('b2b_sales', 15, 2)->nullable()->after('sales');
            $table->decimal('crm_sales', 15, 2)->nullable()->after('b2b_sales');
            $table->decimal('balance_amount', 15, 2)->nullable()->after('closing_rate');
            $table->decimal('gross_revenue', 15, 2)->nullable()->after('balance_amount');
            $table->decimal('fee_admin', 15, 2)->nullable()->after('gross_revenue');
            $table->timestamps();
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
