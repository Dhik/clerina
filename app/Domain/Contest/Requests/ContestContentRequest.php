<?php

namespace App\Domain\Contest\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContestContentRequest extends FormRequest
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
            'contest_id' => ['required', 'exists:contests,id'],
            'link' => ['required', 'url'],
            'rate' => ['required', 'numeric', 'min:1']
        ];
    }
}
