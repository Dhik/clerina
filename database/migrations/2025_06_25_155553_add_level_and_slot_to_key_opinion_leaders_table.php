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
        Schema::table('key_opinion_leaders', function (Blueprint $table) {
            $table->string('level')->nullable()->after('status_affiliate');
            $table->integer('slot')->nullable()->after('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_opinion_leaders', function (Blueprint $table) {
            $table->dropColumn(['level', 'slot']);
        });
    }
};
