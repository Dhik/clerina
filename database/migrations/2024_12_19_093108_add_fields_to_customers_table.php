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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('username')->nullable()->after('phone_number');
            $table->text('shipping_address')->nullable()->after('username');
            $table->string('city')->nullable()->after('shipping_address');
            $table->string('province')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['username', 'shipping_address', 'city', 'province']);
        });
    }
};
