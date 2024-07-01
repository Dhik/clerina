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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();;
            $table->integer('shift_id')->nullable();
            $table->string('compensation')->nullable();
            $table->string('before_shift_overtime_duration')->nullable();
            $table->string('before_shift_break_duration')->nullable();
            $table->string('after_shift_overtime_duration')->nullable();
            $table->string('after_shift_break_duration')->nullable();
            $table->text('note')->nullable();
            $table->string('file')->nullable();
            $table->string('status_approval')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
