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
        Schema::create('affiliate_talents', function (Blueprint $table) {
            $table->id();
            $table->string('no_document')->nullable();
            $table->string('username')->nullable();
            $table->string('pic')->nullable();
            $table->integer('gmv_bottom')->nullable();
            $table->integer('gmv_top')->nullable();
            $table->string('contact_ig')->nullable();
            $table->string('contact_wa_notelp')->nullable();
            $table->string('contact_tiktok')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('platform_menghubungi')->nullable();
            $table->string('status_call')->nullable();
            $table->integer('rate_card')->nullable();
            $table->integer('rate_card_final')->nullable();
            $table->decimal('roas', 15, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->bigInteger('tenant_id')->unsigned()->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_talents');
    }
};
