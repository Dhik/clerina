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
        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->integer('video_slot')->nullable();
            $table->string('content_type')->nullable();
            $table->string('produk')->nullable();
            $table->integer('rate_final')->nullable();
            $table->string('pic')->nullable();
            $table->string('bulan_running')->nullable();
            $table->string('niche')->nullable();
            $table->integer('followers')->nullable();
            $table->string('talent_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('no_npwp')->nullable();
            $table->date('pengajuan_transfer_date')->nullable();
            $table->string('gdrive_ttd_kol_accepting')->nullable();
            $table->string('nik')->nullable();
            $table->integer('price_rate')->nullable();
            $table->integer('first_rate_card')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('slot_final')->nullable();
            $table->integer('tax_deduction')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talents');
    }
};
