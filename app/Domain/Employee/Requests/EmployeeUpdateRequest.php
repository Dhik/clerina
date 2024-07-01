<?php

namespace App\Domain\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employee_id' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'barcode' => ['nullable', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'job_position' => ['required', 'string', 'max:255'],
            'job_level' => ['required', 'string', 'max:255'],
            'join_date' => ['nullable', 'string', 'max:255'],
            'resign_date' => ['nullable', 'date'],
            'status_employee' => ['required', 'string', 'max:255'],
            'end_date' => ['nullable', 'date'],
            'sign_date' => ['nullable', 'date'],
            'birth_date' => ['nullable', 'string', 'max:255'],
            'age' => ['required', 'integer'],
            'birth_place' => ['required', 'string', 'max:255'],
            'citizen_id_address' => ['required', 'string', 'max:255'],
            'residential_address' => ['required', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:255'],
            'ptkp_status' => ['nullable', 'string', 'max:255'],
            'employee_tax_status' => ['nullable', 'string', 'max:255'],
            'tax_config' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account' => ['nullable', 'string', 'max:255'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:255'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:255'],
            'nik_npwp_16_digit' => ['nullable', 'string', 'max:255'],
            'mobile_phone' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'parent_branch_name' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'blood_type' => ['nullable', 'string', 'max:255'],
            'nationality_code' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:255'],
            'length_of_service' => ['nullable', 'integer'],
            'payment_schedule' => ['nullable', 'string', 'max:255'],
            'approval_line' => ['nullable', 'string', 'max:255'],
            'manager' => ['nullable', 'string', 'max:255'],
            'grade' => ['nullable', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'string', 'max:255'],
            'cost_center' => ['nullable', 'string', 'max:255'],
            'cost_center_category' => ['nullable', 'string', 'max:255'],
            'sbu' => ['nullable', 'string', 'max:255'],
            'npwp_16_digit' => ['nullable', 'string', 'max:255'],
            'passport' => ['nullable', 'string', 'max:255'],
            'passport_expiration_date' => ['nullable', 'date'],
        ];
    }
}
