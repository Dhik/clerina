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
        Schema::table('ads_meta', function (Blueprint $table) {
            $table->date('last_updated')->nullable()->after('updated_at');
            $table->date('new_created')->nullable()->after('last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ads_meta', function (Blueprint $table) {
            $table->dropColumn('last_updated');
            $table->dropColumn('new_created');
        });
    }
};
