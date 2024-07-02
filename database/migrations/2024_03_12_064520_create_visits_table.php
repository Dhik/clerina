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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->bigInteger('visit_amount');
            $table->unsignedBigInteger('sales_channel_id')->nullable();
            $table->timestamps();

            $table->foreign('sales_channel_id')
                ->references('id')
                ->on('sales_channels')
                ->onDelete('set null');

            $table->index('date');
            $table->index('sales_channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
