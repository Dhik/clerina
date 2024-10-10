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
            $table->dropColumn('final_transfer');
            $table->unsignedBigInteger('talent_content_id')->nullable()->after('talent_id');
            $table->foreign('talent_content_id')
                  ->references('id')
                  ->on('talent_content')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talent_payments', function (Blueprint $table) {
            $table->double('final_transfer', 10, 2)->nullable()->after('status_payment');
            $table->dropForeign(['talent_content_id']);
            $table->dropColumn('talent_content_id');
        });
    }
};
