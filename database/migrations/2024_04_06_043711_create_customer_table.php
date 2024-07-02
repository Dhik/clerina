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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone_number');
            $table->integer('count_orders');
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('customer_phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_phone_number']);
        });
    }
};
