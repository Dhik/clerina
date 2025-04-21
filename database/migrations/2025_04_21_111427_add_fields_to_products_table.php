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
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->nullable()->after('product');
            $table->string('reference_sku')->nullable()->after('sku');
            $table->decimal('hpp_real', 15, 2)->nullable()->after('harga_batas_bawah');
            $table->decimal('hpp_price', 15, 2)->nullable()->after('hpp_real');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'reference_sku', 'hpp_real', 'hpp_price']);
        });
    }
};
