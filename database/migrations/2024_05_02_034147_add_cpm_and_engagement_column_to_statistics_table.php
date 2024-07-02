<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->decimal('cpm', 30, 2)->default(0)->nullable();
            $table->bigInteger('engagement')->default(0)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('cpm');
            $table->dropColumn('engagement');
        });
    }
};
