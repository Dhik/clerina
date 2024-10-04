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
        Schema::create('talent_payments', function (Blueprint $table) {
            $table->id();
            $table->date('done_payment');
            $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade'); // Foreign key linking to `talents` table
            $table->string('status_payment');
            $table->float('final_transfer', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent_payments');
    }
};
