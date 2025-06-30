<?php

namespace App\Domain\KPIEmployee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIEmployeeRequest extends FormRequest
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
        $rules = [
            'kpi' => 'required|string|max:255',
            'employee_id' => 'required|array',
            'employee_id.*' => 'required|string|exists:employees,employee_id',
            'department' => 'required|in:Sales,Marketing (Ads),Marketing (CRM),Human Capital,KOL Admin,Affiliate Admin,Finance',
            'position' => 'required|in:Leader,Staff',
            'method_calculation' => 'required|in:higher better,lower better',
            'perspective' => 'required|in:Financial,Customer,Business Process,Learn & Growth',
            'data_source' => 'required|string|max:255',
            'target' => 'required|numeric|min:0',
            'bobot' => 'required|numeric|min:0|max:100',
        ];

        // For update actual value
        if ($this->routeIs('kPIEmployee.updateActual')) {
            $rules = [
                'actual' => 'required|numeric|min:0',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'employee_id.required' => 'Please select at least one employee.',
            'employee_id.*.exists' => 'Selected employee does not exist.',
            'department.in' => 'Please select a valid department.',
            'position.in' => 'Please select a valid position.',
            'method_calculation.in' => 'Please select a valid calculation method.',
            'perspective.in' => 'Please select a valid perspective.',
        ];
    }
}