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
            'budget' => 'required|numeric|min:0',
            'kol_percentage' => 'required|numeric|min:0|max:100',
            'ads_percentage' => 'required|numeric|min:0|max:100',
            'creative_percentage' => 'required|numeric|min:0|max:100', 
            'activation_percentage' => 'required|numeric|min:0|max:100',
            'free_product_percentage' => 'required|numeric|min:0|max:100',
            'month' => 'nullable|string|max:7', 
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

            'activation_percentage.required' => 'The Activation percentage field is required.',
            'activation_percentage.numeric' => 'The Activation percentage must be a number.',
            'activation_percentage.min' => 'The Activation percentage must be at least 0.',
            'activation_percentage.max' => 'The Activation percentage must not be greater than 100.',

            'free_product_percentage.required' => 'The Free Product percentage field is required.',
            'free_product_percentage.numeric' => 'The Free Product percentage must be a number.',
            'free_product_percentage.min' => 'The Free Product percentage must be at least 0.',
            'free_product_percentage.max' => 'The Free Product percentage must not be greater than 100.',
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
            'activation_percentage' => 'Activation Percentage',
            'free_product_percentage' => 'Free Product Percentage',
            'month' => 'Month',
            'tenant_id' => 'Tenant ID',
        ];
    }
}
