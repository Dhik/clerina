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
        Schema::table('net_profits', function (Blueprint $table) {
            $table->decimal('b2b_sales', 15, 2)->nullable()->after('sales');
            $table->decimal('crm_sales', 15, 2)->nullable()->after('b2b_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('net_profits', function (Blueprint $table) {
            $table->dropColumn('b2b_sales');
            $table->dropColumn('crm_sales');
        });
    }
};
