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
        Schema::create('customers_facts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penerima')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->decimal('total_order', 10, 2)->nullable();
            $table->integer('total_qty')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->tinyInteger('is_joined')->default(0);
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->unsignedBigInteger('social_media_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_facts');
    }
};
