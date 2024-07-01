<?php

namespace App\Domain\Campaign\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'benefit' => ['required', 'string'],
            'negotiate' => ['required', 'string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
