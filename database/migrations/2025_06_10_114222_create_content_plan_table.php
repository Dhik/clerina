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
        Schema::create('content_plan', function (Blueprint $table) {
            $table->id();
            $table->date('created_date')->nullable();
            $table->date('target_posting_date')->nullable();
            $table->string('status')->nullable();
            $table->string('objektif')->nullable();
            $table->string('jenis_konten')->nullable();
            $table->string('pillar')->nullable();
            $table->string('sub_pillar')->nullable();
            $table->string('talent')->nullable();
            $table->string('venue')->nullable();
            $table->text('hook')->nullable();
            $table->string('produk')->nullable();
            $table->string('referensi')->nullable();
            $table->string('platform')->nullable();
            $table->string('akun')->nullable();
            $table->string('kerkun')->nullable();
            $table->text('brief_konten')->nullable();
            $table->text('caption')->nullable();
            $table->text('link_raw_content')->nullable();
            $table->string('assignee_content_editor')->nullable();
            $table->string('link_hasil_edit')->nullable();
            $table->string('input_link_posting')->nullable();
            $table->date('posting_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_plan');
    }
};
