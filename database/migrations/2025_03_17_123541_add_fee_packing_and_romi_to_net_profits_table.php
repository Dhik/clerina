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
            $table->decimal('fee_packing', 15, 2)->nullable()->after('hpp');
            $table->decimal('romi', 30, 2)->nullable()->after('roas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('net_profits', function (Blueprint $table) {
            $table->dropColumn('fee_packing');
            $table->dropColumn('romi');
        });
    }
};
