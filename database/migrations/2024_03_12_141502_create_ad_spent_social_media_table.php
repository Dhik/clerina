<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_spent_social_media', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('amount');
            $table->unsignedBigInteger('social_media_id')->nullable();
            $table->timestamps();

            $table->foreign('social_media_id')
                ->references('id')
                ->on('social_media')
                ->onDelete('set null');

            $table->index('date');
            $table->index('social_media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spent_social_media');
    }
};
