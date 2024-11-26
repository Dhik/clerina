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
        Schema::table('spent_targets', function (Blueprint $table) {
            $table->decimal('activation_percentage', 5, 2)->nullable()->after('affiliate_percentage');
            $table->decimal('free_product_percentage', 5, 2)->nullable()->after('activation_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spent_targets', function (Blueprint $table) {
            $table->dropColumn('activation_percentage');
            $table->dropColumn('free_product_percentage');
        });
    }
};
