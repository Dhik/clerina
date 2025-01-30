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
            $table->decimal('roas', 30, 2)->nullable();
            $table->bigInteger('visit')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->bigInteger('order')->nullable();
            $table->decimal('closing_rate', 30, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('net_profits', function (Blueprint $table) {
            $table->dropColumn([
                'roas',
                'visit',
                'qty',
                'order',
                'closing_rate'
            ]);
        });
    }
};
