<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('key_opinion_leaders', function (Blueprint $table) {
            $table->id();
            $table->string('channel');
            $table->string('username');
            $table->string('niche');
            $table->integer('average_view');
            $table->string('skin_type');
            $table->string('skin_concern');
            $table->string('content_type');
            $table->integer('rate');
            $table->unsignedBigInteger('pic_contact')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('pic_contact')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->integer('cpm');
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('npwp')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('nik')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('product_delivery')->nullable();
            $table->text('product')->nullable();
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_opinion_leaders');
    }
};
