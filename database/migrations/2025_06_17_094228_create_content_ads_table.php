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
        Schema::create('content_ads', function (Blueprint $table) {
            $table->id();
            $table->string('link_ref')->nullable();
            $table->text('desc_request')->nullable();
            $table->string('product')->nullable();
            $table->string('platform')->nullable();
            $table->string('funneling')->nullable();
            $table->date('request_date')->nullable();
            $table->string('link_drive')->nullable();
            $table->string('status')->nullable();
            $table->string('filename')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_ads');
    }
};
