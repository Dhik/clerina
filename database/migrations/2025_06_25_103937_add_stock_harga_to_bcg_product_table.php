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
        Schema::table('bcg_product', function (Blueprint $table) {
            $table->integer('stock')->nullable()->after('sales');
            $table->integer('harga')->nullable()->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bcg_product', function (Blueprint $table) {
            $table->dropColumn(['stock', 'harga']);
        });
    }
};
