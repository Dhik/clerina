<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add sku to talent_content table
        Schema::table('talent_content', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id');
        });

        // Add sku to campaign_contents table
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop sku from talent_content table
        Schema::table('talent_content', function (Blueprint $table) {
            $table->dropColumn('sku');
        });

        // Drop sku from campaign_contents table
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
