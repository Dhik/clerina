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
        Schema::create('kpi_employee', function (Blueprint $table) {
            $table->id();
            $table->string('kpi')->nullable();
            $table->string('employee_id')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('method_calculation')->nullable();
            $table->string('perspective')->nullable();
            $table->string('data_source')->nullable();
            $table->decimal('target', 15, 2)->nullable();
            $table->decimal('actual', 15, 2)->nullable();
            $table->decimal('bobot', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_employee');
    }
};
