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
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->unsignedBigInteger('view')->nullable()->after('tenant_id');
            $table->unsignedBigInteger('like')->nullable()->after('view');
            $table->unsignedBigInteger('comment')->nullable()->after('like');
            $table->decimal('cpm', 30, 2)->nullable()->after('comment');
            $table->unsignedBigInteger('engagement')->nullable()->after('cpm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->dropColumn(['view', 'like', 'comment', 'cpm', 'engagement']);
        });
    }
};
