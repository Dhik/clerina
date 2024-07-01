<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\OfferEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewOfferRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'rate_final_slot' => ['required', 'numeric', 'min:0', 'lte:rate_total_slot'],
            'rate_total_slot' => ['numeric'],
            'npwp' => ['boolean']
        ];

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }
}
