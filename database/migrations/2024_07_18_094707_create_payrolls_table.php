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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('gaji_pokok')->nullable();
            $table->integer('tunjangan_jabatan')->nullable();
            $table->integer('insentif_live')->nullable();
            $table->integer('insentif')->nullable();
            $table->integer('function')->nullable();
            $table->integer('BPJS')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
