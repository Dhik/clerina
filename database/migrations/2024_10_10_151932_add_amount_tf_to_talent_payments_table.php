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
        Schema::table('talent_payments', function (Blueprint $table) {
            $table->double('amount_tf', 15, 2)->nullable()->after('status_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_payments', function (Blueprint $table) {
            $table->dropColumn('amount_tf');
        });
    }
};
