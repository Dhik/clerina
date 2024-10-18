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
        Schema::table('talent_content', function (Blueprint $table) {
            $table->tinyInteger('is_refund')->default(0)->after('final_rate_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_content', function (Blueprint $table) {
            $table->dropColumn('is_refund');
        });
    }
};
