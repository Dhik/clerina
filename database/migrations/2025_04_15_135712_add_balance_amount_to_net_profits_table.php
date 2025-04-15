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
            $table->decimal('balance_amount', 15, 2)->nullable()->after('closing_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('net_profits', function (Blueprint $table) {
            $table->dropColumn('balance_amount');
        });
    }
};
