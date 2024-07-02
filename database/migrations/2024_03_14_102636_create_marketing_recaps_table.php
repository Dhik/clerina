<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_recaps', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('total_marketing')->nullable();
            $table->bigInteger('total_branding')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_recaps');
    }
};
