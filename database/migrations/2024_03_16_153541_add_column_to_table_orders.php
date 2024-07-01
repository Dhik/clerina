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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receipt_number');
            $table->string('shipment');
            $table->string('payment_method')->nullable();
            $table->string('sku');
            $table->string('variant')->nullable();
            $table->bigInteger('price');
            $table->bigInteger('discount_price');
            $table->string('username')->nullable();
            $table->text('shipping_address');
            $table->string('city')->nullable();
            $table->string('province')->nullable();

            $table->text('product')->change();
            $table->dropColumn('sales_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('receipt_number');
            $table->dropColumn('shipment');
            $table->dropColumn('payment_method');
            $table->dropColumn('sku');
            $table->dropColumn('variant');
            $table->dropColumn('price');
            $table->dropColumn('discount_price');
            $table->dropColumn('username');
            $table->dropColumn('shipping_address');
            $table->dropColumn('city');
            $table->dropColumn('province');

            $table->string('product')->change();
            $table->bigInteger('sales_amount');
        });
    }
};
