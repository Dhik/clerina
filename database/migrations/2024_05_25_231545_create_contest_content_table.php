<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contest_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained();
            $table->text('link')->nullable();
            $table->bigInteger('view')->default(0);
            $table->bigInteger('like')->default(0);
            $table->bigInteger('comment')->default(0);
            $table->bigInteger('share')->default(0);
            $table->bigInteger('interaction')->default(0);
            $table->date('upload_date')->nullable();
            $table->bigInteger('duration')->nullable();
            $table->bigInteger('follower')->nullable();
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('rate')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_contents');
    }
};
