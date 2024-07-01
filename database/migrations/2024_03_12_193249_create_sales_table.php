<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('visit')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->bigInteger('order')->nullable();
            $table->decimal('closing_rate', 30, 2)->nullable();
            $table->bigInteger('turnover')->nullable();
            $table->bigInteger('ad_spent_social_media')->nullable();
            $table->bigInteger('ad_spent_market_place')->nullable();
            $table->bigInteger('ad_spent_total')->nullable();
            $table->decimal('roas', 30, 2)->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
