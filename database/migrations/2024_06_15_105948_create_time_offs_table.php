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
        Schema::create('time_offs', function (Blueprint $table) {
            $table->id();
            $table->string('time_off_type')->nullable();;
            $table->date('date')->nullable();;
            $table->string('request_type')->nullable();;
            $table->string('reason')->nullable();;
            $table->string('delegate_to')->nullable();;
            $table->string('file')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('status_approval')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_offs');
    }
};
