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
        Schema::create('funnel_totals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('total_reach')->nullable();
            $table->bigInteger('total_impression')->nullable();
            $table->bigInteger('total_engagement')->nullable();
            $table->bigInteger('total_cpm')->nullable();
            $table->decimal('total_roas', 30, 2)->nullable();
            $table->bigInteger('total_spend')->nullable();
            $table->text('screenshot')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funnel_totals');
    }
};
