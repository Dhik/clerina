<?php

namespace App\Domain\Marketing\Requests;

use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketingCategoryRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(MarketingCategoryTypeEnum::Category)],
        ];
    }
}
