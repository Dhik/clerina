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
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id'); // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('employee_id'); // Added employee_id field
            $table->string('full_name');
            $table->string('barcode')->nullable();
            $table->string('organization');
            $table->string('job_position');
            $table->string('job_level');
            $table->date('join_date');
            $table->date('resign_date')->nullable();
            $table->string('status_employee');
            $table->date('end_date')->nullable();
            $table->date('sign_date')->nullable();
            $table->string('email')->unique();
            $table->date('birth_date');
            $table->integer('age');
            $table->string('birth_place');
            $table->string('citizen_id_address');
            $table->string('residential_address');
            $table->string('npwp')->nullable();
            $table->string('ptkp_status')->nullable();
            $table->string('employee_tax_status')->nullable();
            $table->string('tax_config')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('bpjs_ketenagakerjaan')->nullable();
            $table->string('bpjs_kesehatan')->nullable();
            $table->string('nik_npwp_16_digit')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('phone')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('parent_branch_name')->nullable();
            $table->string('religion')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('nationality_code')->nullable();
            $table->string('currency')->nullable();
            $table->integer('length_of_service')->nullable();
            $table->string('payment_schedule')->nullable();
            $table->string('approval_line')->nullable();
            $table->string('manager')->nullable();
            $table->string('grade')->nullable();
            $table->string('class')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('cost_center_category')->nullable();
            $table->string('sbu')->nullable();
            $table->string('npwp_16_digit')->nullable();
            $table->string('passport')->nullable();
            $table->date('passport_expiration_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Include soft deletes
            $table->bigInteger('shift_id')->unsigned()->nullable();

            // Foreign key constraint
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
