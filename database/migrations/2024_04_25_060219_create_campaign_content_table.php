<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained();
            $table->foreignId('key_opinion_leader_id')->constrained();
            $table->string('channel');
            $table->string('task_name');
            $table->text('link')->nullable();
            $table->unsignedBigInteger('rate_card');
            $table->string('product');
            $table->date('upload_date')->nullable();
            $table->text('boost_code')->nullable();
            $table->boolean('is_fyp')->nullable()->default(false);
            $table->boolean('is_product_deliver')->nullable()->default(false);
            $table->boolean('is_paid')->nullable()->default(false);
            $table->text('caption')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->index('product');
            $table->index('tenant_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contents');
    }
};
