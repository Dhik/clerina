<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_content_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('view')->default(0);
            $table->bigInteger('like')->default(0);
            $table->bigInteger('comment')->default(0);
            $table->bigInteger('tenant_id');
            $table->timestamps();

            $table->index('date');
            $table->index('campaign_id');
            $table->index('campaign_content_id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
