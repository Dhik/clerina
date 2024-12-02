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
        Schema::create('talent_content', function (Blueprint $table) {
            $table->id(); 
            $table->date('transfer_date')->nullable();
            $table->foreignId('talent_id')->constrained('talents')->onDelete('cascade');
            $table->date('dealing_upload_date')->nullable();
            $table->date('posting_date')->nullable();
            $table->boolean('done')->default(0);
            $table->text('upload_link')->nullable();
            $table->string('pic_code')->nullable();
            $table->string('boost_code')->nullable();
            $table->boolean('kerkun')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent_content');
    }
};
