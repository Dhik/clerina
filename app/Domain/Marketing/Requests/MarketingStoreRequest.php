<?php

namespace App\Domain\Marketing\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketingStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'marketing_category_id' => 'required|exists:marketing_categories,id',
            'marketing_sub_category_id' => 'required|exists:marketing_sub_categories,id',
            'amount' => 'required|numeric|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
