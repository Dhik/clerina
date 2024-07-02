<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_id');
            $table->string('status');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('financed_by')->nullable();
            $table->dateTime('financed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('key_opinion_leader_id')->nullable()->constrained();
            $table->unsignedBigInteger('rate_per_slot')->nullable();
            $table->text('benefit');
            $table->string('negotiate');
            $table->integer('acc_slot')->default(0);
            $table->unsignedBigInteger('rate_total_slot')->nullable();
            $table->unsignedBigInteger('rate_final_slot')->nullable();
            $table->unsignedBigInteger('discount')->nullable();
            $table->boolean('npmp')->default(false);
            $table->unsignedBigInteger('pph')->nullable();
            $table->unsignedBigInteger('final_amount')->nullable();
            $table->text('sign_url')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('nik')->nullable();
            $table->string('transfer_status')->nullable();
            $table->date('transfer_date')->nullable();
            $table->unsignedBigInteger('tenant_id');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
