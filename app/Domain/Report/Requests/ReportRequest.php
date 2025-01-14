<?php

namespace App\Domain\Report\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'platform' => 'required|string|max:255',
            'link' => 'required|string',  // Changed from 'required|url' to 'required|string'
            'month' => 'required|string'
        ];

        // Add thumbnail validation only when creating or updating with new thumbnail
        if ($this->isMethod('POST') || $this->hasFile('thumbnail')) {
            $rules['thumbnail'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
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
            'thumbnail.max' => 'The thumbnail must not be greater than 2MB.',
            'thumbnail.mimes' => 'The thumbnail must be a file of type: jpeg, png, jpg, gif.',
            'link.required' => 'The Tableau embed code is required.',
        ];
    }
}