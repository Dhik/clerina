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
        Schema::create('request_change_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->date('date')->nullable();
            $table->string('starts_shift')->nullable();
            $table->string('end_shift')->nullable();
            $table->string('status_approval')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('clocktime')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_change_shifts');
    }
};
