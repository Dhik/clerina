<?php

namespace App\Domain\Campaign\Requests;

use App\Domain\Campaign\Enums\OfferEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferStatusRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'status' => ['required', Rule::in(OfferEnum::Status)],
        ];

        // Add condition to change 'min' rule for 'acc_slot' if 'status' is 'approved'
        if ($this->input('status') === OfferEnum::Approved) {
            $rules['acc_slot'] = ['required_if:status,'.OfferEnum::Approved, 'numeric', 'min:1'];
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }
}
