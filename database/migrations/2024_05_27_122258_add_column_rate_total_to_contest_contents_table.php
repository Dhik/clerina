<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contest_contents', function (Blueprint $table) {
            $table->unsignedBigInteger('rate_total')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('contest_contents', function (Blueprint $table) {
            $table->dropColumn('rate_total');
        });
    }
};
