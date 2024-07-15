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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('kk')->nullable()->after('profile_picture');
            $table->string('ktp')->nullable()->after('kk');
            $table->string('ijazah')->nullable()->after('ktp');
            $table->string('cv')->nullable()->after('ijazah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['kk', 'ktp', 'ijazah', 'cv']);
        });
    }
};
