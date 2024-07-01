<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('budget');
            $table->bigInteger('used_budget')->default(0);
            $table->dateTime('last_update')->nullable();
            $table->integer('total_creator')->default(0);
            $table->integer('total_content')->default(0);
            $table->integer('cumulative_views')->default(0);
            $table->integer('counted_views')->default(0);
            $table->decimal('interaction', 30, 2)->nullable();
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
