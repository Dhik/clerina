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
        Schema::create('postings', function (Blueprint $table) {
            $table->id();
            $table->integer('play_count');
            $table->integer('comment_count');
            $table->integer('digg_count');
            $table->integer('share_count');
            $table->integer('collect_count');
            $table->integer('download_count');
            $table->string('aweme_id');
            $table->integer('keyword_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postings');
    }
};
