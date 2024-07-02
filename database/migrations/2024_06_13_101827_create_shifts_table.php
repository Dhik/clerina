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
        Schema::create('shifts', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('shift_name', 255); // VARCHAR(255) and NOT NULL
            $table->string('schedule_in', 255); // VARCHAR(255) and NOT NULL
            $table->string('schedule_out', 255); // VARCHAR(255) and NOT NULL
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
