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
            $table->bigInteger('tenant_id')->unsigned()->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
        });
        Schema::table('talent_content', function (Blueprint $table) {
            $table->bigInteger('tenant_id')->unsigned()->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
        Schema::table('talent_content', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
