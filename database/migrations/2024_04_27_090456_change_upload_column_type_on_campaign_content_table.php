<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->dateTime('upload_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_contents', function (Blueprint $table) {
            $table->date('upload_date')->nullable()->change();
        });
    }
};
