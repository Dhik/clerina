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
        Schema::table('daily_hpp', function (Blueprint $table) {
            $table->bigInteger('tenant_id')->unsigned()->nullable()->after('HPP');
        });

        Schema::table('current_hpp', function (Blueprint $table) {
            $table->bigInteger('tenant_id')->unsigned()->nullable()->after('hpp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_hpp', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('current_hpp', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};
