<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->bigInteger('view')->default(0);
            $table->bigInteger('like')->default(0);
            $table->bigInteger('comment')->default(0);
            $table->integer('total_influencer')->default(0);
            $table->integer('total_content')->default(0);
            $table->bigInteger('total_expense')->default(0);
            $table->decimal('achievement', 30, 2)->default(0);
            $table->decimal('cpm', 30, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('view');
            $table->dropColumn('like');
            $table->dropColumn('comment');
            $table->dropColumn('total_influencer');
            $table->dropColumn('total_content');
            $table->dropColumn('total_expense');
            $table->dropColumn('achievement', 30, 2);
            $table->dropColumn('cpm', 30, 2);
        });
    }
};
