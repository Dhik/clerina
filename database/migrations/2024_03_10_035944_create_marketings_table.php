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
        Schema::create('marketings', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('type', ['marketing', 'branding']);
            $table->unsignedBigInteger('marketing_category_id')->nullable();
            $table->unsignedBigInteger('marketing_sub_category_id')->nullable();
            $table->bigInteger('amount');
            $table->timestamps();

            $table->foreign('marketing_category_id')
                ->references('id')
                ->on('marketing_categories')
                ->onDelete('set null');

            $table->foreign('marketing_sub_category_id')
                ->references('id')
                ->on('marketing_sub_categories')
                ->onDelete('set null');

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketings');
    }
};
