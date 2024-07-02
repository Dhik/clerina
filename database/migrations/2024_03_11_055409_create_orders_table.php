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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('id_order');
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone_number')->nullable();
            $table->string('product')->nullable();
            $table->integer('qty');
            $table->bigInteger('sales_amount');
            $table->timestamps();

            $table->foreign('sales_channel_id')
                ->references('id')
                ->on('sales_channels')
                ->onDelete('set null');

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
