<?php

namespace App\Domain\SpentTarget\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpentTargetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;  // Return true if you want to allow all users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'budget' => 'required|numeric|min:0',  // Budget is required, must be numeric, and cannot be negative
            'kol_percentage' => 'required|numeric|min:0|max:100',  // KOL Percentage should be between 0 and 100
            'ads_percentage' => 'required|numeric|min:0|max:100',  // Ads Percentage should be between 0 and 100
            'creative_percentage' => 'required|numeric|min:0|max:100',  // Creative Percentage should be between 0 and 100
            'month' => 'required|string|max:7',  // Month is required, should be in format "YYYY-MM"
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'budget.required' => 'The budget field is required.',
            'budget.numeric' => 'The budget must be a number.',
            'budget.min' => 'The budget must be at least 0.',
            'kol_percentage.required' => 'The KOL percentage field is required.',
            'kol_percentage.numeric' => 'The KOL percentage must be a number.',
            'kol_percentage.min' => 'The KOL percentage must be at least 0.',
            'kol_percentage.max' => 'The KOL percentage must not be greater than 100.',
            'ads_percentage.required' => 'The Ads percentage field is required.',
            'ads_percentage.numeric' => 'The Ads percentage must be a number.',
            'ads_percentage.min' => 'The Ads percentage must be at least 0.',
            'ads_percentage.max' => 'The Ads percentage must not be greater than 100.',
            'creative_percentage.required' => 'The Creative percentage field is required.',
            'creative_percentage.numeric' => 'The Creative percentage must be a number.',
            'creative_percentage.min' => 'The Creative percentage must be at least 0.',
            'creative_percentage.max' => 'The Creative percentage must not be greater than 100.',
            'month.required' => 'The month field is required.',
            'month.string' => 'The month must be a valid string.',
            'month.max' => 'The month format should be "YYYY-MM".',
        ];
    }

    /**
     * Get the validation attributes.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'budget' => 'Budget',
            'kol_percentage' => 'KOL Percentage',
            'ads_percentage' => 'Ads Percentage',
            'creative_percentage' => 'Creative Percentage',
            'month' => 'Month',
            'tenant_id' => 'Tenant ID',
        ];
    }
}
