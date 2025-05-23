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
            $table->float('engagement_rate')->nullable()->after('following');
            $table->string('program')->nullable()->after('engagement_rate');
            $table->boolean('views_last_9_post')->nullable()->after('program');
            $table->boolean('activity_posting')->nullable()->after('views_last_9_post');
            $table->string('status_affiliate')->nullable()->after('activity_posting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('key_opinion_leaders', function (Blueprint $table) {
            $table->dropColumn([
                'engagement_rate',
                'program',
                'views_last_9_post',
                'activity_posting',
                'status_affiliate'
            ]);
        });
    }
};
