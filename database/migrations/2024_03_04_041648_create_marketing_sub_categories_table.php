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
        Schema::create('marketing_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_category_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('marketing_category_id')
                ->references('id')
                ->on('marketing_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_sub_categories');
    }
};
